<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function () {
                try {
                    app(\App\Services\Admin\DashboardService::class)->clearCache();
                } catch (\Throwable $e) {
                }
            });
        }
    }

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
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
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

    public function scopeActive($query)
    {
        $now = now();
        $today = $now->toDateString();
        return $query->where('status', \App\Constants\SubscriptionStatus::ACTIVE)
            ->where(function ($q) use ($now, $today) {
                $q->where(function ($q2) use ($today) {
                    $q2->whereNull('end_at')->where('end_date', '>=', $today);
                })->orWhere(function ($q2) use ($now) {
                    $q2->whereNotNull('end_at')->where('end_at', '>=', $now);
                });
            });
    }

    public function isActive(): bool
    {
        if ($this->status !== \App\Constants\SubscriptionStatus::ACTIVE) {
            return false;
        }
        if ($this->end_at !== null) {
            return $this->end_at >= now();
        }
        return $this->end_date >= now()->toDateString();
    }
}
