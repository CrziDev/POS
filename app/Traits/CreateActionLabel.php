<?php 

namespace App\Traits;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

trait CreateActionLabel
{
    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
            ->label('Save')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.create_another.label'))
            ->action('createAnother')
            ->label('Add New')
            ->keyBindings(['mod+shift+s'])
            ->color('gray');
    }

    //  protected function getCrea(): Action
    // {
    //     return Action::make('create')
    //         ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
    //         ->label('Save')
    //         ->submit('create')
    //         ->keyBindings(['mod+s']);
    // }
}