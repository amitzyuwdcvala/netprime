<?php

namespace App\Constants;

class UserStatus
{
    const ACTIVE   = 'active';
    const INACTIVE = 'inactive';
    const BLOCKED  = 'blocked';

    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::BLOCKED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::ACTIVE   => 'Active',
            self::INACTIVE => 'Inactive',
            self::BLOCKED  => 'Blocked',
        ];
    }
}
