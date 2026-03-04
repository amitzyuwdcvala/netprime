<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\PaymentTransaction;
use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentGatewayManager;
use App\Constants\PaymentStatus;
use App\Constants\SubscriptionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    use ApiResponses;

    private $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Create payment order
     */
    public function create_order_service($request)
    {
        try {
            DB::beginTransaction();

            // User set by AndroidAuth middleware from android_id (header/body) – no Sanctum
            $user = $request->user();
            $planId = $request->input('plan_id');
            $androidId = $user->android_id;

            Log::info('[CreateOrder] create_order_service started', ['android_id' => $androidId, 'plan_id' => $planId]);

            // Check if user already has active subscription
            $activeSubscription = UserSubscription::where('android_id', $androidId)
                ->where('status', SubscriptionStatus::ACTIVE)
                ->where('end_date', '>=', now()->toDateString())
                ->first();

            if ($activeSubscription) {
                Log::warning('[CreateOrder] User already has active subscription', ['android_id' => $androidId]);
                return $this->badRequestResponse([], 'You already have an active subscription. Only one active plan is allowed at a time.');
            }

            // Prevent multiple concurrent orders: reject if user has a non-terminal transaction
            $pendingTransaction = PaymentTransaction::where('android_id', $androidId)
                ->whereIn('status', [
                    PaymentStatus::INITIATED,
                    PaymentStatus::PENDING,
                    PaymentStatus::PENDING_WEBHOOK,
                ])
                ->first();

            if ($pendingTransaction) {
                Log::warning('[CreateOrder] User has pending payment', [
                    'android_id' => $androidId,
                    'transaction_id' => $pendingTransaction->transaction_id,
                ]);
                return $this->badRequestResponse([], 'You have a payment in progress. Please complete or wait for it to expire before creating a new order.');
            }

            Log::info('[CreateOrder] No active subscription, proceeding');

            // Get plan
            $plan = SubscriptionPlan::find($planId);
            if (!$plan || !$plan->is_active) {
                Log::warning('[CreateOrder] Plan not found or inactive', ['plan_id' => $planId]);
                return $this->notFoundResponse([], 'Subscription plan not found or inactive');
            }
            Log::info('[CreateOrder] Plan found', ['plan_id' => $planId, 'amount' => $plan->amount]);

            // Get active payment gateway
            try {
                $gatewayService = $this->gatewayManager->getActiveService();
                Log::info('[CreateOrder] Active gateway resolved', ['gateway' => $this->gatewayManager->getActiveGateway()->name]);
            } catch (\Exception $e) {
                Log::error('[CreateOrder] No active gateway', ['error' => $e->getMessage()]);
                return $this->errorResponse([], 'No active payment gateway found. Please contact support.', 503);
            }

            // Generate unique transaction ID
            $transactionId = 'TXN_' . strtoupper(Str::random(16)) . '_' . time();
            Log::info('[CreateOrder] Generated transaction_id', ['transaction_id' => $transactionId]);

            // Create payment transaction
            $transaction = PaymentTransaction::create([
                'android_id' => $androidId,
                'plan_id' => $planId,
                'payment_gateway_id' => $this->gatewayManager->getActiveGateway()->id,
                'transaction_id' => $transactionId,
                'amount' => $plan->amount,
                'currency' => 'INR',
                'status' => PaymentStatus::INITIATED,
                'metadata' => [
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                ],
            ]);
            Log::info('[CreateOrder] Transaction created in DB', ['transaction_id_primary' => $transaction->id, 'transaction_id' => $transactionId]);

            // Create order with gateway
            $metadata = [
                'transaction_id' => $transactionId,
                'android_id' => $androidId,
                'plan_id' => $planId,
                'plan_name' => $plan->name,
            ];

            Log::info('[CreateOrder] Calling gateway createOrder', ['metadata' => $metadata]);

            $gatewayResponse = $gatewayService->createOrder(
                (float) $plan->amount,
                'INR',
                $metadata
            );

            Log::info('[CreateOrder] Gateway order created', ['gateway_response' => $gatewayResponse]);

            // Update transaction with gateway order ID
            $transaction->gateway_order_id = $gatewayResponse['order_id'];
            $transaction->gateway_response = $gatewayResponse['gateway_response'];
            $transaction->save();
            Log::info('[CreateOrder] Transaction updated with gateway details');

            DB::commit();
            Log::info('[CreateOrder] Order created successfully');

            return $this->successResponse([
                'message' => 'Order created successfully',
                'data' => [
                    'transaction_id'     => $transaction->id,
                    'gateway_order_id'   => $gatewayResponse['order_id'],
                    'payment_session_id' => $gatewayResponse['payment_session_id'] ?? null,
                    'amount'             => (float) $plan->amount,
                    'currency'           => 'INR',
                    'gateway'            => $this->gatewayManager->getActiveGateway()->name,
                    'gateway_key'        => $gatewayResponse['key'] ?? null, // Razorpay key_id for Android SDK
                    'gateway_response'   => $gatewayResponse['gateway_response'],
                    // PayU specific
                    'payment_url'        => $gatewayResponse['payment_url']    ?? null,
                    'payment_params'     => $gatewayResponse['payment_params'] ?? null,
                    // PhonePe specific
                    'checkout_url'       => $gatewayResponse['checkout_url']   ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[CreateOrder] create_order_service exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'android_id' => $request->user()?->android_id,
                'plan_id' => $request->input('plan_id'),
            ]);

            return $this->errorResponse([], 'Failed to create order. Please try again.', 500);
        }
    }

    /**
     * Verify payment (client-initiated)
     * Sets status to pending_webhook, webhook will set to success
     */
    public function verify_payment_service($request)
    {
        try {
            DB::beginTransaction();

            $androidId = $request->input('android_id');
            $transactionIdInput = $request->input('transaction_id');
            $gatewayPaymentId = $request->input('gateway_payment_id');
            $gatewaySignature = $request->input('gateway_signature');
            $gatewayOrderId = $request->input('gateway_order_id');

            // Get transaction: create-order returns $transaction->id (UUID), so accept either UUID (id) or TXN_xxx (transaction_id column)
            $transaction = null;
            if (str_contains($transactionIdInput, '-') && strlen($transactionIdInput) === 36) {
                $transaction = PaymentTransaction::where('id', $transactionIdInput)
                    ->where('android_id', $androidId)
                    ->first();
            }
            if (!$transaction) {
                $transaction = PaymentTransaction::where('transaction_id', $transactionIdInput)
                    ->where('android_id', $androidId)
                    ->first();
            }

            if (!$transaction) {
                return $this->notFoundResponse([], 'Transaction not found');
            }

            // Check if already processed (idempotency)
            if ($transaction->isProcessed()) {
                return $this->successResponse([
                    'message' => 'Payment already verified',
                    'data' => [
                        'subscription' => $this->getSubscriptionData($transaction->android_id),
                    ],
                ]);
            }

            // Get gateway service
            $gatewayService = $this->gatewayManager->resolveService($transaction->gateway);

            // Verify payment
            $paymentData = [
                'gateway_order_id' => $gatewayOrderId,
                'gateway_payment_id' => $gatewayPaymentId,
                'gateway_signature' => $gatewaySignature,
            ];

            $isVerified = $gatewayService->verifyPayment($paymentData);

            if (!$isVerified) {
                $transaction->status = PaymentStatus::FAILED;
                $transaction->error_message = 'Payment verification failed';
                $transaction->failed_at = now();
                $transaction->save();

                DB::commit();

                return $this->badRequestResponse([], 'Payment verification failed. Please try again or contact support.');
            }

            // Update transaction (set to pending_webhook, webhook will finalize)
            $transaction->gateway_payment_id = $gatewayPaymentId;
            $transaction->gateway_signature = $gatewaySignature;
            $transaction->status = PaymentStatus::PENDING_WEBHOOK;
            $transaction->save();

            DB::commit();

            return $this->successResponse([
                'message' => 'Payment verified successfully. Subscription will be activated shortly.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'status' => 'pending_webhook',
                    'note' => 'Your subscription will be activated once webhook is received from payment gateway.',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentService verify_payment_service error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return $this->errorResponse([], 'Failed to verify payment. Please try again.', 500);
        }
    }

    /**
     * Process successful payment (called by webhook or verify API)
     * Uses pessimistic lock to prevent double processing when verify and webhook run concurrently.
     */
    public function processSuccessfulPayment(PaymentTransaction $transaction): bool
    {
        try {
            DB::beginTransaction();

            // Re-fetch with lock so concurrent verify + webhook don't both pass isProcessed()
            $locked = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->first();
            if (!$locked) {
                DB::rollBack();
                return false;
            }
            $transaction = $locked;

            // Idempotency: skip if already processed
            if ($transaction->isProcessed()) {
                DB::rollBack();
                return true;
            }

            $plan = $transaction->plan;
            $user = $transaction->user;

            // Calculate dates
            $startDate = now()->toDateString();
            $endDate = now()->addDays($plan->days)->toDateString();

            // Deactivate any existing subscriptions
            UserSubscription::where('android_id', $user->android_id)
                ->where('status', SubscriptionStatus::ACTIVE)
                ->update(['status' => SubscriptionStatus::EXPIRED]);

            // Create or update subscription
            UserSubscription::updateOrCreate(
                [
                    'android_id' => $user->android_id,
                    'gateway_order_id' => $transaction->gateway_order_id,
                ],
                [
                    'plan_id' => $plan->id,
                    'payment_gateway_id' => $transaction->payment_gateway_id,
                    'gateway_payment_id' => $transaction->gateway_payment_id,
                    'paid_amount' => $transaction->amount,
                    'days' => $plan->days,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => SubscriptionStatus::ACTIVE,
                ]
            );

            // Update user VIP status
            $user->is_vip = true;
            $user->save();

            // Update transaction status and clear any stale error from a previous failed verify
            $transaction->status = PaymentStatus::SUCCESS;
            $transaction->paid_at = now();
            $transaction->error_message = null;
            $transaction->error_code = null;
            $transaction->failed_at = null;
            $transaction->save();

            DB::commit();

            Log::info('Payment processed successfully', [
                'transaction_id' => $transaction->id,
                'android_id' => $user->android_id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentService processSuccessfulPayment error: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get subscription data for response
     */
    private function getSubscriptionData(string $androidId): ?array
    {
        $subscription = UserSubscription::where('android_id', $androidId)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->first();

        if (!$subscription) {
            return null;
        }

        return [
            'id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'start_date' => $subscription->start_date->format('Y-m-d'),
            'end_date' => $subscription->end_date->format('Y-m-d'),
            'status' => $subscription->status,
        ];
    }
}
