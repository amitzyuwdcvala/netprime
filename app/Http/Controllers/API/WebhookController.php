<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Models\PaymentTransaction;
use App\Services\Payment\PaymentGatewayManager;
use App\Services\API\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    use ApiResponses;

    private $gatewayManager;
    private $paymentService;

    public function __construct(PaymentGatewayManager $gatewayManager, PaymentService $paymentService)
    {
        $this->gatewayManager = $gatewayManager;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Razorpay webhook
     */
    public function razorpay(Request $request)
    {
        return $this->handleWebhook($request, 'razorpay');
    }

    /**
     * Handle PayU webhook
     */
    public function payu(Request $request)
    {
        return $this->handleWebhook($request, 'payu');
    }

    /**
     * Handle PhonePe webhook
     */
    public function phonepe(Request $request)
    {
        return $this->handleWebhook($request, 'phonepe');
    }

    /**
     * Handle Cashfree webhook
     */
    public function cashfree(Request $request)
    {
        return $this->handleWebhook($request, 'cashfree');
    }

    /**
     * Generic webhook handler
     */
    private function handleWebhook(Request $request, string $gatewayName)
    {
        try {
            // Get webhook signature from header
            $signature = $request->header('X-Razorpay-Signature') 
                ?? $request->header('X-PayU-Signature')
                ?? $request->header('X-PhonePe-Signature')
                ?? $request->header('X-Cashfree-Signature')
                ?? null;

            $webhookData = $request->all();
            $rawBody = $request->getContent();

            // PayU sends hash in body, not header
            if (strtolower($gatewayName) === 'payu' && $signature === null && !empty($webhookData['hash'])) {
                $signature = $webhookData['hash'];
            }

            // Get active gateway
            $gateway = $this->gatewayManager->getActiveGateway();

            if (!$gateway || strtolower($gateway->name) !== strtolower($gatewayName)) {
                Log::warning("Webhook received for inactive gateway: {$gatewayName}");
                return response()->json(['status' => 'ignored'], 200);
            }

            // Get gateway service
            $gatewayService = $this->gatewayManager->resolveService($gateway);

            // Verify webhook signature (pass raw body so Razorpay can verify against exact bytes signed by gateway)
            if ($signature && !$gatewayService->verifyWebhookSignature($webhookData, $signature, $rawBody)) {
                Log::warning("Invalid webhook signature for gateway: {$gatewayName}", [
                    'signature' => $signature,
                ]);
                return response()->json(['status' => 'invalid_signature'], 400);
            }

            // Process webhook data
            $paymentInfo = $gatewayService->handleWebhook($webhookData);

            // Find transaction by gateway_order_id or gateway_payment_id
            $transaction = PaymentTransaction::where('gateway_order_id', $paymentInfo['order_id'])
                ->orWhere('gateway_payment_id', $paymentInfo['payment_id'])
                ->first();

            if (!$transaction) {
                Log::warning("Transaction not found for webhook", [
                    'gateway' => $gatewayName,
                    'order_id' => $paymentInfo['order_id'] ?? null,
                    'payment_id' => $paymentInfo['payment_id'] ?? null,
                ]);
                return response()->json(['status' => 'transaction_not_found'], 200);
            }

            // Check if payment is successful
            if ($paymentInfo['status'] === 'captured' || $paymentInfo['status'] === 'success') {
                // Process successful payment (this sets is_vip = true)
                $this->paymentService->processSuccessfulPayment($transaction);
            } elseif ($paymentInfo['status'] === 'failed') {
                // Update transaction as failed
                $transaction->status = \App\Constants\PaymentStatus::FAILED;
                $transaction->failed_at = now();
                $transaction->error_message = $paymentInfo['error_message'] ?? 'Payment failed';
                $transaction->save();
            }

            // Always return 200 to acknowledge receipt
            return response()->json(['status' => 'received'], 200);

        } catch (\Exception $e) {
            Log::error("Webhook handler error for {$gatewayName}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // Still return 200 to prevent gateway from retrying
            return response()->json(['status' => 'error'], 200);
        }
    }
}

