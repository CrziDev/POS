<?php

namespace App\Enums;

enum PurchaseOrderStatusEnums: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case FORDELIVERY = 'for-delivery';
    case DELIVERED = 'delivered';
    case DELIVERYINPROGRESS = 'delivery-in-progress';

    public static function options(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->value, self::cases())
        );
    }

    public static function getColor($state): string
    {
        return match ($state) {
            self::PENDING->value => 'gray',
            self::APPROVED->value => 'info',
            self::DELIVERED->value => 'success',
            self::DELIVERYINPROGRESS->value => 'warning',
        };
    }
}
