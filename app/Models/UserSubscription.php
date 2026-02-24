<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'android_id',
        'plan_id',
        'payment_gateway_id',
        'gateway_order_id',
        'gateway_payment_id',
        'paid_amount',
        'days',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'android_id', 'android_id');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === \App\Constants\SubscriptionStatus::ACTIVE
            && $this->end_date >= now()->toDateString();
    }
}
