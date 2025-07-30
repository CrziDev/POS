<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus')
                ->modalSubmitActionLabel('Save')
                ->extraModalFooterActions(fn (Actions\CreateAction $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                        ->label('Add new'),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['password'] = bcrypt('default_password_here'); 
                    
                    return $data;
                }),
        ];
    }
}
