<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use App\Traits\HasBackUrl;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    use HasBackUrl;

    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('Save Changes')
            //     ->action('save'),
            // Actions\Action::make('Back')
            //     ->url($this->getResource()::getUrl('index'))
            //     ->color('gray'),
        ];
    }

    // protected function getFormActions(): array
    // {
    //     return []; 
    // }
}
