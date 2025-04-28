<?php

namespace App\Enums;

use Illuminate\Support\str;

enum RolesEnum: string
{
    case ADMIN = 'admin';
    case SALESCLERK = 'sales-clerk';
    case CASHIER = 'cashier';


    public static function toArray(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => Str::Headline($case->value), self::cases())
        );
    }

    public static function enumList(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
