<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class PayUService implements PaymentGatewayInterface
{
    private $credentials;
    private $gateway;

=

    public function setGateway(PaymentGateway $gateway): self
    {
        $this->gateway = $gateway;
        $this->credentials = is_array($gateway->credentials)
            ? $gateway->credentials
            : json_decode($gateway->credentials, true);
        return $this;
    }

    /**
     * Create "order" for PayU: return payment params for Hosted Checkout (form POST to PayU).
     * PayU does not have a create-order API; we generate hash and params for _payment.
     */
    public function createOrder(float $amount, string $currency, array $metadata): array
    {

    }


    public function verifyPayment(array $paymentData): bool
    {
 
    }


    public function handleWebhook(array $webhookData): array
    {

    }


    public function verifyWebhookSignature(array $data, string $signature, ?string $rawBody = null): bool
    {
    }

    public function getCredentials(): array
    {
        return $this->credentials ?? [];
    }
}
