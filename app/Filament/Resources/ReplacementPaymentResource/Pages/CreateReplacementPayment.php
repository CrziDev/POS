<?php

namespace App\Filament\Resources\ReplacementPaymentResource\Pages;

use App\Filament\Resources\ReplacementPaymentResource;
use App\Traits\CreateActionLabel;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReplacementPayment extends CreateRecord
{
     use CreateActionLabel;
    protected static string $resource = ReplacementPaymentResource::class;
}
