<?php

namespace App\Filament\Resources\SaleTransactionReturnResource\Pages;

use App\Filament\Resources\SaleTransactionReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSaleTransactionReturns extends ListRecords
{
    protected static string $resource = SaleTransactionReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Return Item'),
        ];
    }
}
