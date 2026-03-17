<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService implements PaymentGatewayInterface
{
    private $api;
    private $credentials;
    private $gateway;

    public function __construct()
    {
        // Gateway will be set via setGateway method
    }

    /**
     * Set payment gateway and initialize API
     */
    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway = $gateway;
        $raw = $gateway->credentials;
        $this->credentials = is_array($raw) ? $raw : json_decode($raw, true);

        if (!$this->credentials || !is_array($this->credentials)) {
            throw new \Exception('Invalid payment gateway credentials');
        }

        $this->api = new Api(
            $this->credentials['key_id'] ?? '',
            $this->credentials['key_secret'] ?? ''
        );

        return $this;
    }

    public function createOrder(float $amount, string $currency, array $metadata): array
    {
        try {
            // Attach helpful metadata to Razorpay order. We also include a static
            // app identifier so the same Razorpay account can distinguish
            // between different apps (e.g. FlixyGO vs others).
            $notes = $metadata;
            $notes['app_info'] = 'flixygo';

            $orderData = [
                'receipt' => $metadata['transaction_id'] ?? uniqid('txn_'),
                'amount'  => (int) ($amount * 100),
                'currency' => $currency,
                'notes'   => $notes,
            ];

            $order = $this->api->order->create($orderData);

            return [
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'key' => $this->credentials['key_id'],
                'gateway_response' => $order,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay createOrder error: ' . $e->getMessage(), [
                'metadata' => $metadata,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function verifyPayment(array $paymentData): bool
    {
        try {
            $attributes = [
                'razorpay_order_id' => $paymentData['gateway_order_id'],
                'razorpay_payment_id' => $paymentData['gateway_payment_id'],
                'razorpay_signature' => $paymentData['gateway_signature'],
            ];

            $this->api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (\Exception $e) {
            Log::error('Razorpay verifyPayment error: ' . $e->getMessage(), [
                'payment_data' => $paymentData,
            ]);
            return false;
        }
    }

    public function handleWebhook(array $webhookData): array
    {
        try {
            $event = $webhookData['event'] ?? null;
            $payload = $webhookData['payload'] ?? [];
            $paymentEntity = $payload['payment']['entity'] ?? [];
            $method = $paymentEntity['method'] ?? null;
            $card = $paymentEntity['card'] ?? [];
            $cardLast4 = $card['last4'] ?? null;
            $cardNetwork = $card['network'] ?? null;
            $upiId = $paymentEntity['vpa'] ?? null;

            return [
                'event' => $event,
                'payment_id' => $paymentEntity['id'] ?? null,
                'order_id' => $paymentEntity['order_id'] ?? null,
                'status' => $paymentEntity['status'] ?? null,
                'amount' => isset($paymentEntity['amount']) ? ($paymentEntity['amount'] / 100) : null, // Convert from paise
                'currency' => $paymentEntity['currency'] ?? null,
                'method' => $method,
                'gateway' => $paymentEntity['gateway'] ?? null,
                'card_last4' => $cardLast4,
                'card_network' => $cardNetwork,
                'upi_id' => $upiId,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay handleWebhook error: ' . $e->getMessage(), [
                'webhook_data' => $webhookData,
            ]);
            throw $e;
        }
    }

    public function verifyWebhookSignature(array $data, string $signature, ?string $rawBody = null): bool
    {
        try {
            $webhookSecret = $this->credentials['webhook_secret'] ?? '';

            if (empty($webhookSecret)) {
                Log::warning('Razorpay webhook secret not configured');
                return false;
            }

            // Razorpay signs the raw request body; re-encoding parsed JSON can change key order/whitespace and break verification
            $payload = ($rawBody !== null && $rawBody !== '') ? $rawBody : json_encode($data);
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

            $valid = hash_equals($expectedSignature, $signature);
            if ($valid) {
                Log::info('Razorpay webhook signature verified', []);
            }
            return $valid;
        } catch (\Exception $e) {
            Log::error('Razorpay verifyWebhookSignature error: ' . $e->getMessage());
            return false;
        }
    }

    public function getCredentials(): array
    {
        return $this->credentials ?? [];
    }
}
