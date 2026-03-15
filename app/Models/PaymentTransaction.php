<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    //
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'android_id',
        'plan_id',
        'payment_gateway_id',
        'transaction_id',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_signature',
        'amount',
        'currency',
        'status',
        'payment_method',
        'card_last4',
        'card_network',
        'upi_id',
        'error_code',
        'error_message',
        'error_source',
        'gateway_response',
        'metadata',
        'paid_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'android_id', 'android_id');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', \App\Constants\PaymentStatus::SUCCESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', \App\Constants\PaymentStatus::PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', \App\Constants\PaymentStatus::FAILED);
    }

    public function scopePendingWebhook($query)
    {
        return $query->where('status', 'pending_webhook');
    }

    /**
     * Check if payment is already processed
     */
    public function isProcessed(): bool
    {
        return in_array($this->status, [
            \App\Constants\PaymentStatus::SUCCESS,
            \App\Constants\PaymentStatus::REFUNDED,
        ]);
    }

    protected function formattedAmount(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->currency . ' ' . number_format((float) $this->amount, 2)
        );
    }
}
