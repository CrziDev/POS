<?php

namespace App\Filament\Resources\ReplacementPaymentResource\Pages;

use App\Filament\Resources\ReplacementPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReplacementPayment extends EditRecord
{
    protected static string $resource = ReplacementPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
