<?php

namespace App\Tables\Actions;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use STS\FilamentImpersonate\Concerns\Impersonates;

class CustomImpersonate extends Action
{
    use Impersonates;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('filament-impersonate::action.label'))
            ->iconButton()
            ->icon('impersonate-icon')
            ->action(function ($record) {
                if (!$record->branch()->exists()) {
                    Notification::make()
                        ->title('User Has No Branch')
                        ->body('Please assign a branch for this user.')
                        ->danger()
                        ->send();

                    return;
                }

                $this->impersonate($record->user);
            })
            ->hidden(fn ($record) => !$this->canBeImpersonated($record->user));
    }
}
