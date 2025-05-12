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

        if(!auth()->user()->hasRole(['admin'])){
            $query = $query->where('branch_id',auth()->user()->employee->branch->id);
        }

        if($transction){
            $query = $query->where('Sale_transaction_id',$transction);
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
                        "<span> Sold Price: ". $item->quantity - $item->returned_quantity ."<span>" .
                    "<small>" 
            ]
        )->all();

    }
}
