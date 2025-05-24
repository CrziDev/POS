<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $guarded = [];


    public static function booted():void
    {    
        static::created(function ($model){
            $totalAmount = $model->po->orderedItems()->sum('total_amount');
            $model->po->update([
                'total_amount' => $totalAmount
            ]);
        });

        static::updating(function ($model){
            $totalAmount = $model->price *  $model->quantity;
            
            $model->total_amount = $totalAmount;
        });

        // static::updated(function ($model){
        //     $totalAmount = $model->po->orderedItems()->sum('total_amount');
        //     $model->po->update([
        //         'total_amount' => $totalAmount
        //     ]);
        // });

        static::deleted(function ($model){
            $totalAmount = $model->po->orderedItems()->sum('total_amount');
            $model->po->update([
                'total_amount' => $totalAmount
            ]);
        });
    }

    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }

    public function po(){
        return $this->belongsTo(PurchaseOrder::class,'purchase_order_id');
    }
    
}
