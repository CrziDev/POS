<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Actions\Notifications\PurchaseOrderApproved;
use App\Enums\PurchaseOrderStatusEnums;
use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewPurchaseOrder extends ViewRecord
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

                    $notification = new PurchaseOrderApproved(
                        branchName: $record->branch->name,
                        userName: auth_user()->employee->full_name,
                        route: route('filament.admin.resources.purchase-orders.edit',['record'=>$record->id]),
                        roles: ['manager']
                    );

                    $notification->handle();
                })
                ->visible(fn ($record) =>
                     $record->status == PurchaseOrderStatusEnums::PENDING->value
                     &&
                     isRole('admin')
                ),
            ];
    }
}
