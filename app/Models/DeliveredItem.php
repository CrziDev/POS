<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveredItem extends Model
{
    protected $guarded = [];

    
    public static function booted():void
    {

        static::updating(function ($model){
            $totalAmount = $model->price *  $model->quantity;
            
            $model->total_amount = $totalAmount;
        });
    }

    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }

    public function po(){
        return $this->belongsTo(PurchaseOrder::class,'purchase_order_id');
    }
    
}
