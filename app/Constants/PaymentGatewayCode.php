<?php

namespace App\Constants;

class PaymentGatewayCode
{
    const RAZORPAY = 'razorpay';
    const PHONEPE  = 'phonepe';
    const PAYU     = 'payu';
    const CASHFREE = 'cashfree';

    public static function all(): array
    {
        return [
            self::RAZORPAY,
            self::PHONEPE,
            self::PAYU,
            self::CASHFREE,
        ];
    }

    public static function labels(): array
    {
        return [
            self::RAZORPAY => 'Razorpay',
            self::PHONEPE  => 'PhonePe',
            self::PAYU     => 'PayU',
            self::CASHFREE => 'Cashfree',
        ];
    }
}
