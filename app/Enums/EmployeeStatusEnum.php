<?php

namespace App\Enums;

enum EmployeeStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public static function getColor($state): string
    {
        return match ($state) {
            self::ACTIVE->value  => 'success',
            self::INACTIVE->value  => 'danger',
            default => false,
        };
    }
}
