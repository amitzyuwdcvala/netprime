<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\PaymentTransaction;
use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentGatewayManager;
use App\Constants\PaymentStatus;
use App\Constants\SubscriptionStatus;
use App\Jobs\ProcessPaymentWebhook;
use Illuminate\Support\Facades\Cache;
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
     * Create payment order.
     *
     * No DB transaction record is created here. We only create a gateway order
     * and cache the metadata. The actual PaymentTransaction row is created later
     * when the webhook confirms payment (like weblo's PayPal pattern).
     */
    public function create_order_service($request)
    {
        try {
            $user = $request->user();
            $planId = $request->input('plan_id');
            $androidId = $user->android_id;

            // Log::info('[CreateOrder] started', ['android_id' => $androidId, 'plan_id' => $planId]);

            $activeSubscription = UserSubscription::where('android_id', $androidId)
                ->active()
                ->first();

            if ($activeSubscription) {
                return $this->badRequestResponse([], 'You already have an active subscription.');
            }

            $plan = SubscriptionPlan::find($planId);
            if (!$plan || !$plan->is_active) {
                return $this->notFoundResponse([], 'Subscription plan not found or inactive');
            }

            try {
                $gatewayService = $this->gatewayManager->getActiveService();
                $gateway = $this->gatewayManager->getActiveGateway();
            } catch (\Exception $e) {
                Log::error('[CreateOrder] No active gateway', ['error' => $e->getMessage()]);
                return $this->errorResponse([], 'No active payment gateway found. Please contact support.', 503);
            }

            $transactionId = 'TXN_' . strtoupper(Str::random(16)) . '_' . time();

            $metadata = [
                'transaction_id' => $transactionId,
                'android_id' => $androidId,
                'plan_id' => $planId,
                'plan_name' => $plan->name,
            ];

            $gatewayResponse = $gatewayService->createOrder(
                (float) $plan->amount,
                'INR',
                $metadata
            );

            Log::info('[CreateOrder] Gateway order created', [
                'order_id' => $gatewayResponse['order_id'],
                'transaction_id' => $transactionId,
            ]);

            // Store order metadata in cache (24 hours) so webhook/verify can create the DB row later
            $cacheKey = 'payment_order:' . $gatewayResponse['order_id'];
            $cacheData = [
                'transaction_id' => $transactionId,
                'android_id' => $androidId,
                'plan_id' => $planId,
                'payment_gateway_id' => $gateway->id,
                'amount' => $plan->amount,
                'currency' => 'INR',
                'gateway_order_id' => $gatewayResponse['order_id'],
                'gateway_response' => $gatewayResponse['gateway_response'] ?? [],
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ];
            Cache::put($cacheKey, $cacheData, 86400);

            // Verify cache was stored
            $cacheVerify = Cache::get($cacheKey);
            Log::info('[CreateOrder] Cache stored', [
                'cache_key' => $cacheKey,
                'cache_stored' => $cacheVerify !== null,
                'cache_driver' => config('cache.default'),
            ]);

            return $this->successResponse([
                'message' => 'Order created successfully',
                'data' => [
                    'transaction_id'     => $transactionId,
                    'gateway_order_id'   => $gatewayResponse['order_id'],
                    'payment_session_id' => $gatewayResponse['payment_session_id'] ?? null,
                    'amount'             => (float) $plan->amount,
                    'currency'           => 'INR',
                    'gateway'            => $gateway->name,
                    'gateway_key'        => $gatewayResponse['key'] ?? null,
                    'gateway_response'   => $gatewayResponse['gateway_response'],
                    'payment_url'        => $gatewayResponse['payment_url']    ?? null,
                    'payment_params'     => $gatewayResponse['payment_params'] ?? null,
                    'checkout_url'       => $gatewayResponse['checkout_url']   ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[CreateOrder] exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'android_id' => $request->user()?->android_id,
                'plan_id' => $request->input('plan_id'),
            ]);

            return $this->errorResponse([], 'Failed to create order. Please try again.', 500);
        }
    }

    /**
     * Verify payment (client-initiated after gateway SDK callback).
     *
     * Creates the transaction DB row if it doesn't exist yet, then marks it
     * as pending_webhook. The webhook job finalises to success.
     */
    public function verify_payment_service($request)
    {
        try {
            // Get android_id from authenticated user (header) or body fallback
            $androidId = $request->user()?->android_id ?? $request->input('android_id');
            $gatewayPaymentId = $request->input('gateway_payment_id');
            $gatewaySignature = $request->input('gateway_signature');
            $gatewayOrderId = $request->input('gateway_order_id');

            Log::info('[VerifyService] Starting verification', [
                'android_id' => $androidId,
                'android_id_source' => $request->user() ? 'auth_user' : 'body',
                'gateway_order_id' => $gatewayOrderId,
                'gateway_payment_id' => $gatewayPaymentId,
                'has_signature' => !empty($gatewaySignature),
            ]);

            // Check if cache exists for this order
            $cacheKey = 'payment_order:' . $gatewayOrderId;
            $cachedOrder = Cache::get($cacheKey);
            Log::info('[VerifyService] Cache check', [
                'cache_key' => $cacheKey,
                'cache_exists' => $cachedOrder !== null,
                'cached_android_id' => $cachedOrder['android_id'] ?? null,
            ]);

            // Try to find an existing transaction (webhook may have arrived first)
            // First try with the android_id from request/auth
            $transaction = PaymentTransaction::where('gateway_order_id', $gatewayOrderId)
                ->where('android_id', $androidId)
                ->first();

            // If not found and we have cached data, try with cached android_id
            if (!$transaction && $cachedOrder && $cachedOrder['android_id'] !== $androidId) {
                Log::info('[VerifyService] Trying with cached android_id');
                $transaction = PaymentTransaction::where('gateway_order_id', $gatewayOrderId)
                    ->where('android_id', $cachedOrder['android_id'])
                    ->first();
                if ($transaction) {
                    $androidId = $cachedOrder['android_id'];
                }
            }

            // If still not found, try without android_id filter (order might exist from webhook)
            if (!$transaction) {
                $transaction = PaymentTransaction::where('gateway_order_id', $gatewayOrderId)->first();
                if ($transaction) {
                    Log::info('[VerifyService] Found transaction without android_id filter', [
                        'transaction_android_id' => $transaction->android_id,
                    ]);
                }
            }

            Log::info('[VerifyService] Transaction lookup', [
                'found' => $transaction !== null,
                'transaction_id' => $transaction?->id,
                'status' => $transaction?->status,
            ]);

            if ($transaction && $transaction->isProcessed()) {
                return $this->successResponse([
                    'message' => 'Payment already verified',
                    'data' => [
                        'subscription' => $this->getSubscriptionData($androidId),
                    ],
                ]);
            }

            // Resolve the gateway service from the active gateway
            $gateway = $this->gatewayManager->getActiveGateway();
            $gatewayService = $this->gatewayManager->resolveService($gateway);

            Log::info('[VerifyService] Calling gateway verifyPayment', [
                'gateway' => $gateway->name,
                'gateway_order_id' => $gatewayOrderId,
            ]);

            $isVerified = $gatewayService->verifyPayment([
                'gateway_order_id' => $gatewayOrderId,
                'gateway_payment_id' => $gatewayPaymentId,
                'gateway_signature' => $gatewaySignature,
            ]);

            Log::info('[VerifyService] Gateway verification result', [
                'is_verified' => $isVerified,
                'gateway' => $gateway->name,
            ]);

            if (!$isVerified) {
                if ($transaction) {
                    $transaction->update([
                        'status' => PaymentStatus::FAILED,
                        'error_message' => 'Payment verification failed',
                        'failed_at' => now(),
                    ]);
                }
                return $this->badRequestResponse([], 'Payment verification failed.');
            }

            // Create the transaction row now if it doesn't exist (webhook hasn't arrived yet)
            if (!$transaction) {
                $transaction = $this->createTransactionFromCache($gatewayOrderId);
            }

            if (!$transaction) {
                return $this->notFoundResponse([], 'Order data not found. Please wait a moment and try again.');
            }

            $transaction->update([
                'gateway_payment_id' => $gatewayPaymentId,
                'gateway_signature' => $gatewaySignature,
                'status' => PaymentStatus::PENDING_WEBHOOK,
            ]);

            $gatewayName = strtolower($gateway->name ?? '');
            ProcessPaymentWebhook::dispatch(
                $gatewayName,
                $gatewayOrderId,
                $gatewayPaymentId ?: null,
                'success',
                null
            );
            Log::info('[Verify] Job dispatched (verify confirmed payment)', [
                'order_id' => $gatewayOrderId,
                'transaction_id' => $transaction->id,
                'gateway' => $gatewayName,
            ]);

            Log::info('[VerifyService] SUCCESS - Returning success response', [
                'transaction_id' => $transaction->id,
            ]);

            return $this->successResponse([
                'message' => 'Payment verified successfully. Your subscription is being activated.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'status' => 'pending_webhook',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[VerifyService] Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'android_id' => $request->input('android_id'),
                'gateway_order_id' => $request->input('gateway_order_id'),
            ]);

            return $this->errorResponse([], 'Failed to verify payment. Please try again.', 500);
        }
    }

    /**
     * Create PaymentTransaction row from cached order data.
     * Used by verify and webhook when the DB row doesn't exist yet.
     */
    public function createTransactionFromCache(string $gatewayOrderId): ?PaymentTransaction
    {
        $cacheKey = 'payment_order:' . $gatewayOrderId;
        $cached = Cache::get($cacheKey);

        Log::info('[CreateFromCache] Attempting cache lookup', [
            'cache_key' => $cacheKey,
            'cache_driver' => config('cache.default'),
            'cache_found' => $cached !== null,
        ]);

        if (!$cached) {
            Log::warning('[CreateFromCache] Cache miss', [
                'order_id' => $gatewayOrderId,
                'cache_driver' => config('cache.default'),
            ]);
            return null;
        }

        $transaction = PaymentTransaction::firstOrCreate(
            ['gateway_order_id' => $cached['gateway_order_id']],
            [
                'android_id' => $cached['android_id'],
                'plan_id' => $cached['plan_id'],
                'payment_gateway_id' => $cached['payment_gateway_id'],
                'transaction_id' => $cached['transaction_id'],
                'amount' => $cached['amount'],
                'currency' => $cached['currency'],
                'gateway_response' => $cached['gateway_response'] ?? [],
                'status' => PaymentStatus::PENDING_WEBHOOK,
                'metadata' => [
                    'user_agent' => $cached['user_agent'] ?? null,
                    'ip_address' => $cached['ip_address'] ?? null,
                ],
            ]
        );

        Cache::forget($cacheKey);

        Log::info('[CreateFromCache] Transaction created or found', [
            'transaction_id' => $transaction->id,
            'order_id' => $gatewayOrderId,
            'was_recently_created' => $transaction->wasRecentlyCreated,
        ]);

        return $transaction;
    }

    /**
     * Process successful payment (called by webhook job).
     * Uses pessimistic lock to prevent double processing.
     */
    public function processSuccessfulPayment(PaymentTransaction $transaction): bool
    {
        try {
            DB::beginTransaction();

            $locked = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->first();
            if (!$locked) {
                DB::rollBack();
                return false;
            }
            $transaction = $locked;

            if ($transaction->isProcessed()) {
                DB::rollBack();
                return true;
            }

            $plan = $transaction->plan;
            $user = $transaction->user;

            $startAt = $transaction->paid_at ?? now();
            $endAt = (clone $startAt)->addDays($plan->days);
            $startDate = $startAt->toDateString();
            $endDate = $endAt->toDateString();

            UserSubscription::where('android_id', $user->android_id)
                ->where('status', SubscriptionStatus::ACTIVE)
                ->update(['status' => SubscriptionStatus::EXPIRED]);

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
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'status' => SubscriptionStatus::ACTIVE,
                ]
            );

            $user->is_vip = true;
            $user->save();

            $transaction->status = PaymentStatus::SUCCESS;
            if ($transaction->paid_at === null) {
                $transaction->paid_at = $startAt;
            }
            $transaction->error_message = null;
            $transaction->error_code = null;
            $transaction->failed_at = null;
            $transaction->save();

            DB::commit();

            Log::info('[ProcessPayment] Success', [
                'transaction_id' => $transaction->id,
                'android_id' => $user->android_id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ProcessPayment] Error', [
                'transaction_id' => $transaction->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    private function getSubscriptionData(string $androidId): ?array
    {
        $subscription = UserSubscription::where('android_id', $androidId)
            ->active()
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
