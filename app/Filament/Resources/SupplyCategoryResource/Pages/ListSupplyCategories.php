<?php

namespace App\Filament\Resources\SupplyCategoryResource\Pages;

use App\Filament\Resources\SupplyCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupplyCategories extends ListRecords
{
    protected static string $resource = SupplyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Category')
                 ->modalSubmitActionLabel('Save')
                ->extraModalFooterActions(fn (Actions\CreateAction $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                        ->label('Add new'),
                ])
                ->icon('heroicon-m-plus'),
        ];
    }
}
