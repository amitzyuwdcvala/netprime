<?php

namespace App\Constants;

class PaymentStatus
{
    const PENDING  = 'pending';
    const SUCCESS  = 'success';
    const FAILED   = 'failed';
    const REFUNDED = 'refunded';

    public static function all(): array
    {
        return [
            self::PENDING,
            self::SUCCESS,
            self::FAILED,
            self::REFUNDED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::PENDING  => 'Pending',
            self::SUCCESS  => 'Success',
            self::FAILED   => 'Failed',
            self::REFUNDED => 'Refunded',
        ];
    }
}
