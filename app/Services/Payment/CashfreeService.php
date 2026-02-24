<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashfreeService implements PaymentGatewayInterface
{
    private array $credentials;
    private PaymentGateway $gateway;

    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway     = $gateway;
        $this->credentials = is_array($gateway->credentials)
            ? $gateway->credentials
            : json_decode($gateway->credentials, true);

        return $this;
    }

    private function getBaseUrl(): string
    {
        $env = strtoupper($this->credentials['env'] ?? 'TEST');
        return $env === 'PROD'
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }


    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'x-client-id'     => $this->credentials['app_id'],
            'x-client-secret' => $this->credentials['secret_key'],
            'x-api-version'   => '2025-01-01',
            'Content-Type'    => 'application/json',
        ]);

        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }
    }

    // ─────────────────────────────────────────────
    // CREATE ORDER
    // POST /pg/orders
    // Returns order_id + payment_session_id
    // ─────────────────────────────────────────────

    public function createOrder(float $amount, string $currency, array $metadata): array
    {
        try {
            $orderId = 'CF_' . $metadata['transaction_id'];

            $payload = [
                'order_id'       => $orderId,
                'order_amount'   => round($amount, 2),
                'order_currency' => 'INR',
                'order_note'     => 'Subscription - ' . ($metadata['plan_name'] ?? 'Plan'),
                'customer_details' => [
                    'customer_id'    => $metadata['android_id'],
                    'customer_phone' => '9999999999', // required by Cashfree
                    'customer_email' => 'user@netprime.app',
                    'customer_name'  => 'App User',
                ],
                'order_meta' => [
                    'notify_url' => url('/api/v1/webhook/cashfree'), // webhook url
                ],
                'order_tags' => [
                    'android_id' => $metadata['android_id'],
                    'plan_id'    => $metadata['plan_id'],
                ],
            ];

            $response = $this->http()->post(
                $this->getBaseUrl() . '/orders',
                $payload
            );

            if (!$response->successful()) {
                Log::error('Cashfree createOrder failed', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'payload' => $payload,
                ]);
                throw new \Exception('Cashfree order creation failed: ' . $response->body());
            }

            $data = $response->json();

            Log::info('Cashfree createOrder success', [
                'order_id'           => $data['order_id'],
                'cf_order_id'        => $data['cf_order_id'],
                'payment_session_id' => $data['payment_session_id'],
            ]);

            return [
                'success'            => true,
                'order_id'           => $data['order_id'],           // our CF_TXN_xxx
                'cf_order_id'        => $data['cf_order_id'],        // cashfree internal id
                'payment_session_id' => $data['payment_session_id'], // Android SDK needs this
                'gateway_response'   => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Cashfree createOrder exception: ' . $e->getMessage(), [
                'metadata' => $metadata,
                'trace'    => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // ─────────────────────────────────────────────
    // VERIFY PAYMENT
    // GET /pg/orders/{order_id}
    // Check if order_status === PAID
    // ─────────────────────────────────────────────

    public function verifyPayment(array $paymentData): bool
    {
        try {
            $orderId = $paymentData['gateway_order_id']; // CF_TXN_xxx

            $response = $this->http()->get(
                $this->getBaseUrl() . '/orders/' . $orderId
            );

            if (!$response->successful()) {
                Log::error('Cashfree verifyPayment failed', [
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'order_id' => $orderId,
                ]);
                return false;
            }

            $data   = $response->json();
            $status = strtoupper($data['order_status'] ?? '');

            Log::info('Cashfree verifyPayment status', [
                'order_id' => $orderId,
                'status'   => $status,
            ]);

            // PAID means payment was successful
            return $status === 'PAID';
        } catch (\Exception $e) {
            Log::error('Cashfree verifyPayment exception: ' . $e->getMessage(), [
                'payment_data' => $paymentData,
                'trace'        => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    // ─────────────────────────────────────────────
    // HANDLE WEBHOOK
    // Cashfree POSTs to your notify_url
    // ─────────────────────────────────────────────

    public function handleWebhook(array $webhookData): array
    {
        try {
            $type    = $webhookData['type'] ?? '';
            $data    = $webhookData['data'] ?? [];
            $order   = $data['order']   ?? [];
            $payment = $data['payment'] ?? [];

            $status = match (strtoupper($type)) {
                'PAYMENT_SUCCESS_WEBHOOK'      => 'success',
                'PAYMENT_FAILED_WEBHOOK'       => 'failed',
                'PAYMENT_USER_DROPPED_WEBHOOK' => 'failed',
                default                        => 'pending',
            };

            return [
                'event'      => $type,
                'payment_id' => (string) ($payment['cf_payment_id'] ?? ''),
                'order_id'   => $order['order_id'] ?? '',  // our CF_TXN_xxx
                'status'     => $status,
                'amount'     => $payment['payment_amount'] ?? null,
                'method'     => $payment['payment_method'] ?? null,
                'raw'        => $webhookData,
            ];
        } catch (\Exception $e) {
            Log::error('Cashfree handleWebhook exception: ' . $e->getMessage(), [
                'webhook_data' => $webhookData,
            ]);
            throw $e;
        }
    }

    // ─────────────────────────────────────────────
    // VERIFY WEBHOOK SIGNATURE
    // Formula: base64(HMAC-SHA256(timestamp + rawBody, secret_key))
    // ─────────────────────────────────────────────

    public function verifyWebhookSignature(
        array $data,
        string $signature,
        ?string $rawBody = null
    ): bool {
        try {
            $timestamp  = request()->header('x-webhook-timestamp') ?? '';
            $secretKey  = $this->credentials['secret_key'];
            $body       = ($rawBody !== null && $rawBody !== '')
                ? $rawBody
                : json_encode($data);

            // Cashfree formula
            $signedData = $timestamp . $body;
            $computed   = base64_encode(
                hash_hmac('sha256', $signedData, $secretKey, true)
            );

            $valid = hash_equals($computed, $signature);

            if (!$valid) {
                Log::warning('Cashfree webhook signature mismatch', [
                    'computed'  => $computed,
                    'received'  => $signature,
                    'timestamp' => $timestamp,
                ]);
            }

            return $valid;
        } catch (\Exception $e) {
            Log::error('Cashfree verifyWebhookSignature exception: ' . $e->getMessage());
            return false;
        }
    }

    public function getCredentials(): array
    {
        return $this->credentials ?? [];
    }
}
