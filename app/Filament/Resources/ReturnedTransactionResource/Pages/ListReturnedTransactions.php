<?php

namespace App\Filament\Resources\ReturnedTransactionResource\Pages;

use App\Filament\Resources\ReturnedTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnedTransactions extends ListRecords
{
    protected static string $resource = ReturnedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Return Item'),
        ];
    }
}
