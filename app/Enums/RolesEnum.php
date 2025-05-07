<?php

namespace App\Enums;

use Illuminate\Support\str;

enum RolesEnum: string
{
    case ADMIN = 'admin';
    case SALESCLERK = 'sales-clerk';
    case CASHIER = 'cashier';


    public static function toArray($employee = false): array
    {   
        $cases = self::cases();
        if($employee){
            unset($cases[0]);
        }
        return array_combine(
            array_map(fn($case) => $case->value, $cases),
            array_map(fn($case) => Str::Headline($case->value), $cases)
        );
    }

    public static function enumList(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
