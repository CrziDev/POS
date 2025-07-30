<?php

namespace App\Filament\Resources\DeliveredItemResource\Pages;

use App\Filament\Resources\DeliveredItemResource;
use App\Traits\CreateActionLabel;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveredItem extends CreateRecord
{
    use CreateActionLabel;
    protected static string $resource = DeliveredItemResource::class;
}
