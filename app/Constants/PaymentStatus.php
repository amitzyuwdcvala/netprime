<?php

namespace App\Constants;

class PaymentStatus
{
    const INITIATED      = 'initiated';
    const PENDING        = 'pending';
    const PENDING_WEBHOOK = 'pending_webhook';
    const SUCCESS        = 'success';
    const FAILED         = 'failed';
    const REFUNDED       = 'refunded';

    public static function all(): array
    {
        return [
            self::INITIATED,
            self::PENDING,
            self::PENDING_WEBHOOK,
            self::SUCCESS,
            self::FAILED,
            self::REFUNDED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::INITIATED       => 'Initiated',
            self::PENDING         => 'Pending',
            self::PENDING_WEBHOOK => 'Pending Webhook',
            self::SUCCESS         => 'Success',
            self::FAILED          => 'Failed',
            self::REFUNDED        => 'Refunded',
        ];
    }
}
