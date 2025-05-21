<?php

namespace App\Filament\Resources\ReplacementPaymentResource\Pages;

use App\Filament\Resources\ReplacementPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReplacementPayments extends ListRecords
{
    protected static string $resource = ReplacementPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
