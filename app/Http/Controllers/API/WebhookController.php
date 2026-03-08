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

    public function razorpay(Request $request)
    {
        return $this->handleWebhook($request, 'razorpay');
    }

    public function payu(Request $request)
    {
        return $this->handleWebhook($request, 'payu');
    }

    public function phonepe(Request $request)
    {
        return $this->handleWebhook($request, 'phonepe');
    }

    public function cashfree(Request $request)
    {
        return $this->handleWebhook($request, 'cashfree');
    }

    private function handleWebhook(Request $request, string $gatewayName)
    {
        $requestId = uniqid('wh_', true);
        try {
            Log::info('[Webhook] Incoming', [
                'request_id' => $requestId,
                'gateway' => $gatewayName,
                'event' => $request->input('event') ?? $request->input('type') ?? 'unknown',
            ]);

            $signature = $request->header('X-Razorpay-Signature')
                ?? $request->header('X-PayU-Signature')
                ?? $request->header('X-PhonePe-Signature')
                ?? $request->header('X-Cashfree-Signature')
                ?? null;

            $webhookData = $request->all();
            $rawBody = $request->getContent();

            if (strtolower($gatewayName) === 'payu' && $signature === null && !empty($webhookData['hash'])) {
                $signature = $webhookData['hash'];
            }
            if (strtolower($gatewayName) === 'phonepe' && $signature === null) {
                $signature = $request->header('Authorization');
            }

            $gateway = $this->gatewayManager->getActiveGateway();

            if (!$gateway || strtolower($gateway->name) !== strtolower($gatewayName)) {
                Log::warning('[Webhook] Inactive gateway, ignoring', ['request_id' => $requestId, 'gateway' => $gatewayName]);
                return response()->json(['status' => 'ignored'], 200);
            }

            $gatewayService = $this->gatewayManager->resolveService($gateway);

            if ($signature && !$gatewayService->verifyWebhookSignature($webhookData, $signature, $rawBody)) {
                Log::warning('[Webhook] Invalid signature', ['request_id' => $requestId]);
                return response()->json(['status' => 'invalid_signature'], 400);
            }

            $paymentInfo = $gatewayService->handleWebhook($webhookData);

            Log::info('[Webhook] Payment info extracted', [
                'request_id' => $requestId,
                'order_id' => $paymentInfo['order_id'] ?? null,
                'payment_id' => $paymentInfo['payment_id'] ?? null,
                'status' => $paymentInfo['status'] ?? null,
            ]);

            $orderId = $paymentInfo['order_id'] ?? null;
            $paymentId = $paymentInfo['payment_id'] ?? null;

            // Look up existing transaction (verify may have created it already)
            $transaction = null;
            if ($orderId) {
                $transaction = PaymentTransaction::where('gateway_order_id', $orderId)->first();
            }
            if (!$transaction && $paymentId) {
                $transaction = PaymentTransaction::where('gateway_payment_id', $paymentId)->first();
            }

            // If no DB row yet, create it from cached order metadata (weblo pattern)
            if (!$transaction && $orderId) {
                $transaction = $this->paymentService->createTransactionFromCache($orderId);
            }

            if (!$transaction) {
                Log::warning('[Webhook] Transaction not found and cache miss', [
                    'request_id' => $requestId,
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                ]);
                return response()->json(['status' => 'transaction_not_found'], 200);
            }

            $update = [];
            $method = $paymentInfo['method'] ?? null;
            if ($method !== null && $method !== '') {
                $methodStr = is_string($method) ? $method : (string) $method;
                if (strlen($methodStr) > 50) {
                    $methodStr = substr($methodStr, 0, 50);
                }
                $update['payment_method'] = $methodStr;
            }
            if (isset($paymentInfo['card_last4'])) {
                $update['card_last4'] = $paymentInfo['card_last4'];
            }
            if (!empty($paymentInfo['card_network'])) {
                $update['card_network'] = $paymentInfo['card_network'];
            }
            if (!empty($paymentInfo['upi_id'])) {
                $update['upi_id'] = $paymentInfo['upi_id'];
            }
            if (!empty($paymentId) && empty($transaction->gateway_payment_id)) {
                $update['gateway_payment_id'] = $paymentId;
            }
            $isSuccess = in_array($paymentInfo['status'] ?? '', ['success', 'captured'], true);
            if ($isSuccess && $transaction->paid_at === null) {
                $update['paid_at'] = now();
            }
            if (!empty($update)) {
                $transaction->update($update);
            }

            ProcessPaymentWebhook::dispatch(
                $gatewayName,
                $orderId,
                $paymentId,
                $paymentInfo['status'] ?? 'unknown',
                $paymentInfo['error_message'] ?? null,
            );

            Log::info('[Webhook] Job dispatched', [
                'request_id' => $requestId,
                'transaction_id' => $transaction->id,
            ]);

            return response()->json(['status' => 'received'], 200);

        } catch (\Exception $e) {
            Log::error('[Webhook] Exception', [
                'request_id' => $requestId ?? null,
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error'], 200);
        }
    }
}

