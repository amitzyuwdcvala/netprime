<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create order with payment gateway
     *
     * @param float $amount Payment amount
     * @param string $currency Currency code (e.g., 'INR')
     * @param array $metadata Additional metadata (transaction_id, user info, etc.)
     * @return array Gateway response with order_id and other details
     */
    public function createOrder(float $amount, string $currency, array $metadata): array;

    /**
     * Verify payment signature
     *
     * @param array $paymentData Payment data from mobile app
     * @return bool True if payment is verified, false otherwise
     */
    public function verifyPayment(array $paymentData): bool;

    /**
     * Handle webhook from gateway
     *
     * @param array $webhookData Webhook payload from gateway
     * @return array Extracted payment information
     */
    public function handleWebhook(array $webhookData): array;

    /**
     * Verify webhook signature
     *
     * @param array $data Webhook data (parsed body)
     * @param string $signature Webhook signature from header
     * @param string|null $rawBody Raw request body (use for HMAC when gateway signs raw body, e.g. Razorpay)
     * @return bool True if signature is valid, false otherwise
     */
    public function verifyWebhookSignature(array $data, string $signature, ?string $rawBody = null): bool;

    /**
     * Get gateway credentials
     *
     * @return array Gateway credentials (key_id, key_secret, etc.)
     */
    public function getCredentials(): array;
}

