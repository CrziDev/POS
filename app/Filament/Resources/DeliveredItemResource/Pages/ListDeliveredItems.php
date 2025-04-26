<?php

namespace App\Filament\Resources\DeliveredItemResource\Pages;

use App\Filament\Resources\DeliveredItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveredItems extends ListRecords
{
    protected static string $resource = DeliveredItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
