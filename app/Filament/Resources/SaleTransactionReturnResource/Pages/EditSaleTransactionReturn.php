<?php

namespace App\Filament\Resources\SaleTransactionReturnResource\Pages;

use App\Filament\Resources\SaleTransactionReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaleTransactionReturn extends EditRecord
{
    protected static string $resource = SaleTransactionReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
