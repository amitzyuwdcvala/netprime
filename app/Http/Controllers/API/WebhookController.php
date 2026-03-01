<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Jobs\ProcessPaymentWebhook;
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
        $requestId = uniqid('wh_', true);
        try {
            Log::info('[Webhook] Incoming request', [
                'request_id' => $requestId,
                'gateway' => $gatewayName,
                'event' => $request->input('event') ?? $request->input('type') ?? 'unknown',
                'payload_size' => strlen($request->getContent()),
            ]);

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
                Log::warning('[Webhook] Inactive gateway, ignoring', [
                    'request_id' => $requestId,
                    'gateway' => $gatewayName,
                ]);
                return response()->json(['status' => 'ignored'], 200);
            }

            // Get gateway service
            $gatewayService = $this->gatewayManager->resolveService($gateway);

            // Verify webhook signature (pass raw body so Razorpay can verify against exact bytes signed by gateway)
            if ($signature && !$gatewayService->verifyWebhookSignature($webhookData, $signature, $rawBody)) {
                Log::warning('[Webhook] Invalid signature', [
                    'request_id' => $requestId,
                    'gateway' => $gatewayName,
                ]);
                return response()->json(['status' => 'invalid_signature'], 400);
            }

            // Process webhook data (extract payment info only; actual processing in job)
            $paymentInfo = $gatewayService->handleWebhook($webhookData);

            Log::info('[Webhook] Payment info extracted', [
                'request_id' => $requestId,
                'gateway' => $gatewayName,
                'order_id' => $paymentInfo['order_id'] ?? null,
                'payment_id' => $paymentInfo['payment_id'] ?? null,
                'status' => $paymentInfo['status'] ?? null,
            ]);

            // Find transaction to ensure it exists before queuing
            $transaction = PaymentTransaction::where('gateway_order_id', $paymentInfo['order_id'])
                ->orWhere('gateway_payment_id', $paymentInfo['payment_id'])
                ->first();

            if (!$transaction) {
                Log::warning('[Webhook] Transaction not found', [
                    'request_id' => $requestId,
                    'gateway' => $gatewayName,
                    'order_id' => $paymentInfo['order_id'] ?? null,
                    'payment_id' => $paymentInfo['payment_id'] ?? null,
                ]);
                $response = response()->json(['status' => 'transaction_not_found'], 200);
                Log::info('[Webhook] Response sent', ['request_id' => $requestId, 'http_status' => 200, 'body_status' => 'transaction_not_found']);
                return $response;
            }

            // Queue processing so we return 200 quickly (reduces timeout risk under load)
            ProcessPaymentWebhook::dispatch(
                $gatewayName,
                $paymentInfo['order_id'] ?? null,
                $paymentInfo['payment_id'] ?? null,
                $paymentInfo['status'] ?? 'unknown',
                $paymentInfo['error_message'] ?? null,
            );

            Log::info('[Webhook] Job dispatched, returning 200', [
                'request_id' => $requestId,
                'gateway' => $gatewayName,
                'order_id' => $paymentInfo['order_id'] ?? null,
                'payment_id' => $paymentInfo['payment_id'] ?? null,
                'transaction_id' => $transaction->id,
            ]);

            $response = response()->json(['status' => 'received'], 200);
            Log::info('[Webhook] Response sent', ['request_id' => $requestId, 'http_status' => 200, 'body_status' => 'received']);
            return $response;

        } catch (\Exception $e) {
            Log::error('[Webhook] Handler exception', [
                'request_id' => $requestId ?? null,
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Still return 200 to prevent gateway from retrying
            return response()->json(['status' => 'error'], 200);
        }
    }
}

