<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransactionItem extends Model
{
    protected $guarded = [];

    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }

    public static function getOptionsArray($transction = false): array
    {
        $query = self::query()->with('supply');

        if($transction){
            $query = $query->where('Sale_transaction_id',$transction);
        }

        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    $item->supply->name
            ])->all();
    }
}
