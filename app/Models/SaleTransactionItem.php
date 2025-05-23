<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransactionItem extends Model
{
    protected $guarded = [];

    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }

    public function saleTransaction(){
        return $this->belongsTo(SaleTransaction::class,'sale_transaction_id');
    }

    public static function getOptionsArray($transction = false,$html = false): array
    {
        $query = self::query()->with('supply');

        if($transction){
            $query = $query->where('sale_transaction_id',$transction);
        }

        if(!$html){

            return $query->get()->mapWithKeys(fn($item) =>
                [
                    $item->id => 
                        $item->supply->name
                ])->all();
        }

         return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> <b>Item:</b> " . $item->supply->name . "</span>". "<br>".
                    "<small>" .
                        "<span> Branch:". $item->saleTransaction->branch->name."<span>"."<br>".
                        "<span> Price: ".  numberToMoney($item->price_amount)."<span>"."<br>".
                        "<span> Remaining: ". $item->quantity - $item->returned_quantity ."<span>" .
                    "<small>" 
            ]
        )->all();

    }
}
