<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $guarded = [];

    
    public static function purchaseOrderRestock($purchaseOrder){

        $purchaseOrder->orderedItems()->each(function($item) use ($purchaseOrder){
            $stock = Stock::firstOrNew(
                ['supply_id' => $item->supply_id,'branch_id'=>$purchaseOrder->branch_id]
            );

            $stock->update([
                'quantity' => $stock->quantity + $item->quantity,
            ]);
        });

    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }
}
