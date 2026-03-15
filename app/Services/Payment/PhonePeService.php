<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\Env;

class PhonePeService implements PaymentGatewayInterface
{
    private array $credentials;
    private PaymentGateway $gateway;
    private StandardCheckoutClient $client;

    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway     = $gateway;
        $this->credentials = is_array($gateway->credentials)
            ? $gateway->credentials
            : json_decode($gateway->credentials, true);

        $env = strtoupper($this->credentials['env'] ?? 'UAT');

        // New v2 SDK initialization
        $this->client = StandardCheckoutClient::getInstance(
            $this->credentials['client_id'],
            (int) ($this->credentials['client_version'] ?? 1),
            $this->credentials['client_secret'],
            $env === 'PROD' ? Env::PRODUCTION : Env::UAT
        );

        return $this;
    }

    public function createOrder(float $amount, string $currency, array $metadata): array
    {
        try {
            $merchantOrderId = 'PP_' . $metadata['transaction_id'];
            $amountInPaise   = (int) round($amount * 100);

            // redirectUrl: where the user's browser goes after payment. For ngrok testing set APP_URL to your ngrok URL.
            $request = (new StandardCheckoutPayRequestBuilder())
                ->merchantOrderId($merchantOrderId)
                ->amount($amountInPaise)
                ->redirectUrl(url('/api/v1/payment/phonepe/callback'))
                ->message('Subscription - ' . ($metadata['plan_name'] ?? 'Plan')) 
                ->build();

            // Create SDK order — returns token for Android
            $response    = $this->client->pay($request);
            $redirectUrl = $response->getRedirectUrl();
            $orderId     = $merchantOrderId; // already set above; SDK response has no getMerchantOrderId()

            Log::info('PhonePe createOrder success', [
                'merchant_order_id' => $orderId,
                'redirect_url'      => $redirectUrl,
            ]);

            return [
                'success'          => true,
                'order_id'         => $orderId,          // PP_TXN_xxx
                'checkout_url'     => $redirectUrl,      // Android SDK needs this
                'gateway_response' => [
                    'merchant_order_id' => $orderId,
                    'redirect_url'      => $redirectUrl,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('PhonePe createOrder error: ' . $e->getMessage(), [
                'metadata' => $metadata,
                'trace'    => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function verifyPayment(array $paymentData): bool
    {
        try {
            $merchantOrderId = $paymentData['gateway_order_id']; // PP_TXN_xxx

            $response = $this->client->getOrderStatus($merchantOrderId);
            $state    = strtoupper($response->getState() ?? '');

            Log::info('PhonePe verifyPayment', [
                'order_id' => $merchantOrderId,
                'state'    => $state,
            ]);

            return $state === 'COMPLETED';
        } catch (\Exception $e) {
            Log::error('PhonePe verifyPayment error: ' . $e->getMessage(), [
                'payment_data' => $paymentData,
            ]);
            return false;
        }
    }


    public function handleWebhook(array $webhookData): array
    {
        try {
            $event   = $webhookData['event'] ?? $webhookData['type'] ?? '';
            $payload = $webhookData['payload'] ?? $webhookData['data'] ?? [];

            $merchantOrderId = $payload['merchantOrderId']
                ?? $payload['order']['merchantOrderId']
                ?? '';

            $paymentDetails = $payload['paymentDetails'] ?? [];
            $firstPayment   = is_array($paymentDetails) && isset($paymentDetails[0])
                ? $paymentDetails[0]
                : $paymentDetails;
            $transactionId = $firstPayment['transactionId']
                ?? $payload['transactionId']
                ?? '';

            $amount = isset($payload['amount'])
                ? (int) $payload['amount'] / 100
                : null;

            $state  = strtoupper($payload['state'] ?? '');
            $status = match (strtolower($event)) {
                'checkout.order.completed' => ($state === 'COMPLETED' ? 'success' : 'pending'),
                'checkout.order.failed'    => 'failed',
                default                    => ($state === 'COMPLETED' ? 'success' : ($state === 'FAILED' ? 'failed' : 'pending')),
            };

            Log::info('PhonePe webhook received', [
                'event'    => $event,
                'order_id' => $merchantOrderId,
                'status'   => $status,
            ]);

            return [
                'event'      => $event,
                'payment_id' => $transactionId,
                'order_id'   => $merchantOrderId, // PP_TXN_xxx
                'status'     => $status,
                'amount'     => $amount,
                'raw'        => $webhookData,
            ];
        } catch (\Exception $e) {
            Log::error('PhonePe handleWebhook error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyWebhookSignature(
        array $data,
        string $signature,
        ?string $rawBody = null
    ): bool {
        try {
            // Build headers array from request
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    $headerKey           = str_replace(
                        ' ',
                        '-',
                        ucwords(strtolower(str_replace('_', ' ', substr($key, 5))))
                    );
                    $headers[$headerKey] = $value;
                }
            }

            $body     = $rawBody ?? json_encode($data);
            $username = $this->credentials['webhook_username'] ?? '';
            $password = $this->credentials['webhook_password'] ?? '';

            // New v2 SDK verification
            $callbackResponse = $this->client->verifyCallbackResponse(
                $headers,
                $body,
                $username,
                $password
            );

            $isValid = $callbackResponse !== null;

            if (!$isValid) {
                Log::warning('PhonePe webhook verification failed');
            }

            return $isValid;
        } catch (\Exception $e) {
            Log::error('PhonePe verifyWebhookSignature error: ' . $e->getMessage());
            return false;
        }
    }

    public function getCredentials(): array
    {
        return $this->credentials ?? [];
    }
}
