<?php

namespace App\Enums;

use Illuminate\Support\str;

enum RolesEnum: string
{
    case OWNER = 'owner';
    case SUPERADMIN = 'super-admin';
    // case SALESCLERK = 'sales-clerk';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';

    public static function toArray($excludeOwner = false): array
    {   
        $cases = self::cases();
        if($excludeOwner){
            unset($cases[0]);
            unset($cases[1]);
        }

        if(auth_user()->hasRole(['manager'])){
            unset($cases[3]);
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
