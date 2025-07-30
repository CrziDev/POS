<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Traits\CreateActionLabel;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    use CreateActionLabel;
    protected static string $resource = SaleResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
        
    }
}
