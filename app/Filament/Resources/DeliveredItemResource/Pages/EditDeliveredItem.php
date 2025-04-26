<?php

namespace App\Filament\Resources\DeliveredItemResource\Pages;

use App\Filament\Resources\DeliveredItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveredItem extends EditRecord
{
    protected static string $resource = DeliveredItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
