<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentGatewayManager
{
    /**
     * Get active payment gateway
     * Only one gateway should be active at a time. Uses sort_order so the result is deterministic.
     */
    public function getActiveGateway(): ?PaymentGateway
    {
        return Cache::remember('active_payment_gateway', 600, function () {
            return PaymentGateway::where('is_active', true)
                ->orderBy('sort_order')
                ->first();
        });
    }

    /**
     * Resolve payment gateway service based on gateway name
     */
    public function resolveService(PaymentGateway $gateway): PaymentGatewayInterface
    {
        $gatewayName = strtolower($gateway->name);

        return match ($gatewayName) {
            'razorpay' => app(RazorpayService::class)->setGateway($gateway),
            'payu' => app(PayUService::class)->setGateway($gateway),
            'phonepe' => app(PhonePeService::class)->setGateway($gateway),
            'cashfree' => app(CashfreeService::class)->setGateway($gateway),
            default => throw new \Exception("Unsupported payment gateway: {$gatewayName}"),
        };
    }

    /**
     * Get active gateway service
     */
    public function getActiveService(): PaymentGatewayInterface
    {
        $gateway = $this->getActiveGateway();
        
        if (!$gateway) {
            throw new \Exception('No active payment gateway found. Please activate a payment gateway in admin panel.');
        }

        return $this->resolveService($gateway);
    }

    /**
     * Clear active gateway cache (call after gateway activation/deactivation)
     */
    public function clearCache(): void
    {
        Cache::forget('active_payment_gateway');
    }
}

