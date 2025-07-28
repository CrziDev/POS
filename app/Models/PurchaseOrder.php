<?php

namespace App\Models;

use App\Actions\Notifications\PurchaseOrderCreated;
use App\Enums\PurchaseOrderStatusEnums;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $guarded = [];

    public static function booted():void
    {
        static::created(function ($model){
            $notification = new PurchaseOrderCreated(
                branchName: $model->branch->name,
                userName: $model->preparedBy->employee->full_name,
                route: route('filament.admin.resources.purchase-orders.view',['record'=>$model->id]),
                roles: ['owner','super-admin']
            );

            $notification->handle();
        });

    }

    public function approvePurchaseOrder()
    {
        $this->update(['status'=>PurchaseOrderStatusEnums::APPROVED->value]);
    }
    
    public function initiateDelivery()
    {
        $this->update(['status'=>PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value]);
    }

    public function acceptDelivery()
    {
        $this->update([
            'status'=>PurchaseOrderStatusEnums::DELIVERED->value
        ]);
    }

    public function addPendingDeliveryItems()
    {
        $this->orderedItems()->each(function($item){
            DeliveredItem::create(
                [
                    'purchase_order_id' => $this->id,
                    'supply_id'         => $item->supply_id,
                    'quantity'          => $item->quantity,
                    'price'             => $item->price,
                    'total_amount'      => $item->total_amount,
                    'status'            => 'pending'
                ]
            );
        });
    }

    public function getDeliveredAmountAttribute(){
        return $this->deliveredItems()->where('status','delivered')->sum('total_amount');
    }
    
    public function orderedItems(){
        return $this->hasMany(PurchaseOrderItem::class,'purchase_order_id');
    }

    public function deliveredItems(){
        return $this->hasMany(DeliveredItem::class,'purchase_order_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    
    public function preparedBy(){
        return $this->belongsTo(User::class,'prepared_by');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_id');
    }

}
