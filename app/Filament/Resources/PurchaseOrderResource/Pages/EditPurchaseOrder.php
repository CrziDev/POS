<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Enums\PurchaseOrderStatusEnums;
use App\Filament\Resources\PurchaseOrderResource;
use App\Models\Stock;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('approve-delivery')
                ->label('Approve Purchase Order')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Purchase Order')
                ->modalDescription('Are you sure you want to approve this purchase order? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, Approve')
                ->action(function (Model $record) {
                    $record->approvePurchaseOrder();
                    notification('The purchase order has been successfully approved.');
                })
                ->visible(fn ($record) => $record->status == PurchaseOrderStatusEnums::PENDING->value),
            
            Action::make('create-delivery')
                ->label('Initiate Delivery Process')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->modalHeading('Initiate Delivery')
                ->requiresConfirmation()
                ->modalDescription('Are you sure you want to create a pending delivery for this order?')
                ->modalSubmitActionLabel('Yes, Proceed')
                ->action(function (Model $record) {
                    $record->initiateDelivery();
                    notification('Delivery has been initiated for this purchase order.');
                })
                ->visible(fn ($record) => $record->status == PurchaseOrderStatusEnums::APPROVED->value),
            
                Action::make('accept-delivery')
                ->label('Confirm Delivery')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Confirm Delivery Receipt')
                ->modalDescription('Please confirm that all items in this purchase order have been delivered and priced correctly.')
                ->modalSubmitActionLabel('Confirm Delivery')
                ->before(function ($record, $action) {
                    $hasUnpricedItems = $record->orderedItems()->where('price', 0)->exists();
            
                    if ($hasUnpricedItems) {
                        Notification::make()
                            ->title('Delivery Not Confirmed')
                            ->body('Some items do not have prices set. Please ensure all delivered items are priced before confirming.')
                            ->danger()
                            ->send();
            
                        $action->cancel();
                    }
                })
                ->action(function (Model $record) {
                    $record->acceptDelivery();

                    ## Add to Stocks
                    Stock::purchaseOrderRestock($record);
                    
                    Notification::make()
                        ->title('Delivery Confirmed')
                        ->body('The delivery has been successfully confirmed. Stocks Has Been Updated')
                        ->success()
                        ->send();

                    return redirect(route('filament.admin.resources.purchase-orders.index'));
                })
                ->visible(fn ($record) => $record->status === PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value),
            
        ];
    }


    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->hidden(function (): bool {
                return ($this->record->status == PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value)?true:false;
            });
    }

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
            ->hidden(function (): bool {
                return ($this->record->status == PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value)?true:false;
            });
    }
}
