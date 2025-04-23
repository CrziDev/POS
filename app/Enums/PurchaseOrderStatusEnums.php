<?php

namespace App\Enums;

enum PurchaseOrderStatusEnums: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case FORDELIVERY = 'for-delivery';
    case DELIVERED = 'delivered';
    case DELIVERYINPROGRESS = 'delivery-in-progress';
    

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
