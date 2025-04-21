<?php

namespace App\Enums;

enum PurchaseOrderStatusEnums: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case FORDELIVERY = 'for-delivery';
    case DELIVERED = 'delivered';

}
