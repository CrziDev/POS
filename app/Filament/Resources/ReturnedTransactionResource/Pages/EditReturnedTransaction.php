<?php

namespace App\Filament\Resources\ReturnedTransactionResource\Pages;

use App\Filament\Resources\ReturnedTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnedTransaction extends EditRecord
{
    protected static string $resource = ReturnedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
