<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $guarded = [];

    
    public static function purchaseOrderRestock($purchaseOrder){

        $purchaseOrder->deliveredItems()->each(function($item) use ($purchaseOrder){
            $stock = Stock::firstOrNew(
                ['supply_id' => $item->supply_id,'branch_id'=>$purchaseOrder->branch_id]
            );

            $stock->update([
                'quantity' => $stock->quantity + $item->quantity,
            ]);

            $item->update([
                'status' => 'Delivered',
            ]);
            
        });

    }

    public static function getOptionsArray(): array
    {
        $query = self::query()->with(['supply','branch']);

        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> <b>Supply:</b> " . $item->supply->name . "</span>". "<br>".
                    "<small>" .
                        "<span> Branch:". $item->branch->name."<span>"."<br>".
                        "<span> Stocks: ". $item->quantity."<span>" .
                    "<small>" 
            ]
        )->all();
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }
}
