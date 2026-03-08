<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Primary key is android_id (string, not UUID)
     */
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'android_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'android_id',
        'is_vip',
        'video_click_count',
        'added_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_vip' => 'boolean',
            'video_click_count' => 'integer',
            'added_by' => 'integer',
        ];
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'android_id';
    }

    /**
     * Get active subscription for user
     */
    public function subscription()
    {
        return $this->hasOne(UserSubscription::class, 'android_id', 'android_id')
            ->active();
    }

    /**
     * Get all subscriptions for user
     */
    public function subscriptions()
    {
        return $this->hasOne(UserSubscription::class, 'android_id', 'android_id');
    }

    /**
     * Get payment transactions for user
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'android_id', 'android_id');
    }

    /**
     * Get active subscription (alias for subscription method)
     */
    public function activeSubscription()
    {
        return $this->subscription();
    }

    /**
     * Check if user has active VIP subscription
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->is_vip) {
            return false;
        }

        return $this->activeSubscription()->exists();
    }

    /**
     * Check if subscription is expired
     */
    public function isSubscriptionExpired(): bool
    {
        $subscription = $this->subscriptions;

        if (!$subscription) {
            return true;
        }

        return !$subscription->isActive();
    }

    /**
     * Determine if this user was created manually by an admin.
     */
    public function isManuallyAdded(): bool
    {
        return !is_null($this->added_by);
    }
}

