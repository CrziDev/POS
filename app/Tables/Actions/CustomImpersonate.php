<?php

namespace App\Tables\Actions;

use App\Enums\RolesEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Http\RedirectResponse;
use Lab404\Impersonate\Services\ImpersonateManager;
use Livewire\Features\SupportRedirects\Redirector;
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

    public function impersonate($record): bool|Redirector|RedirectResponse
    {
        if (!$this->canBeImpersonated($record)) {
            return false;
        }

        session()->put([
            'impersonate.back_to' => $this->getBackTo() ?? request('fingerprint.path', request()->header('referer')) ?? Filament::getCurrentPanel()->getUrl(),
            'impersonate.guard' => $this->getGuard()
        ]);

        app(ImpersonateManager::class)->take(
            Filament::auth()->user(),
            $record,
            $this->getGuard()
        );

         if($record->hasRole([RolesEnum::CASHIER->value])){
            return redirect('pos-panel'); 
        } 

        return redirect($this->getRedirectTo());
    }

}
