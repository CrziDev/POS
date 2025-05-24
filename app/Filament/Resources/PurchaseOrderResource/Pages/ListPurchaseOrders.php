<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(isRole('manager'))
                ->icon('heroicon-o-plus')
                ->label('Create Purchase Order')
                ->successNotificationTitle('Created Purchase Order Successfully')
                ->successRedirectUrl(fn (Model $record): string => route('filament.admin.resources.purchase-orders.edit', [
                    'record' => $record,
                ]))
        ];
    }
}
