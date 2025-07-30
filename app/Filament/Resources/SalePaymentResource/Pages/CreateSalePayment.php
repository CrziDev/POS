<?php

namespace App\Filament\Resources\SalePaymentResource\Pages;

use App\Filament\Resources\SalePaymentResource;
use App\Traits\CreateActionLabel;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSalePayment extends CreateRecord
{
    use CreateActionLabel;
    protected static string $resource = SalePaymentResource::class;
}
