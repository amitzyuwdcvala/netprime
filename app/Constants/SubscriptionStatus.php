<?php

namespace App\Constants;

class SubscriptionStatus
{
    const ACTIVE    = 'active';
    const EXPIRED   = 'expired';
    const CANCELLED = 'cancelled';
    const PENDING   = 'pending';

    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::EXPIRED,
            self::CANCELLED,
            self::PENDING,
        ];
    }

    public static function labels(): array
    {
        return [
            self::ACTIVE    => 'Active',
            self::EXPIRED   => 'Expired',
            self::CANCELLED => 'Cancelled',
            self::PENDING   => 'Pending',
        ];
    }
}
