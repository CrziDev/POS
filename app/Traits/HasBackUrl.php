<?php 

namespace App\Traits;

use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

trait HasBackUrl
{
    public ?string $backUrl = null;

    public function mount($record=null): void
    {
        $this->backUrl = url()->previous();
        parent::mount($record);
    }

    protected function getRedirectUrl(): string  // <- return type is string to be compatible with EditRecord & CreateRecord
    {
        return $this->backUrl;
    }


    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Back')
            ->url(fn(Page $livewire) => $livewire->backUrl ?? static::getResource()::getUrl());
    }
}