<?php

namespace App\Filament\Resources\SupplyUnitResource\Pages;

use App\Filament\Resources\SupplyUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplyUnit extends EditRecord
{
    protected static string $resource = SupplyUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
