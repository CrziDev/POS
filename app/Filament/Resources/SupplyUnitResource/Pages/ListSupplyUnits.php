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
                 ->modalSubmitActionLabel('Save')
                ->extraModalFooterActions(fn (Actions\CreateAction $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                        ->label('Add new'),
                ])
                ->icon('heroicon-m-plus'),
        ];
    }
}
