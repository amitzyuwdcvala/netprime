<?php

namespace App\Jobs;

use App\Constants\PaymentStatus;
use App\Models\PaymentTransaction;
use App\Services\Admin\DashboardService;
use App\Services\API\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $gatewayName,
        public ?string $orderId,
        public ?string $paymentId,
        public string $status,
        public ?string $errorMessage = null,
    ) {}

    public function handle(PaymentService $paymentService): void
    {

        $transaction = PaymentTransaction::where('gateway_order_id', $this->orderId)
            ->orWhere('gateway_payment_id', $this->paymentId)
            ->first();

        if (!$transaction) {
            Log::warning('[ProcessPaymentWebhook] Transaction not found', [
                'gateway' => $this->gatewayName,
                'order_id' => $this->orderId,
                'payment_id' => $this->paymentId,
            ]);
            return;
        }

        if ($this->status === 'captured' || $this->status === 'success') {
            $ok = $paymentService->processSuccessfulPayment($transaction);
        } elseif ($this->status === 'failed') {
            $transaction->status = PaymentStatus::FAILED;
            $transaction->failed_at = now();
            $transaction->error_message = $this->errorMessage ?? 'Payment failed';
            $transaction->save();
        }

        // Invalidate dashboard cache so stats reflect new payment/subscription state
        app(DashboardService::class)->clearCache();
    }
}
