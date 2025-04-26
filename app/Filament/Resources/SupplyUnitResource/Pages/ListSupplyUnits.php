<?php

namespace App\Filament\Resources\SupplyUnitResource\Pages;

use App\Filament\Resources\SupplyUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupplyUnits extends ListRecords
{
    protected static string $resource = SupplyUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Unit')
                ->icon('heroicon-m-plus'),
        ];
    }
}
