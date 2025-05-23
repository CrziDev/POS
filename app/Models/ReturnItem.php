<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $guarded = [];

     public function saleTransactionItem(){
        return $this->belongsTo(SaleTransactionItem::class,'original_item_id');
    }
    
    public function replacementItems(){
        return $this->hasMany(ReplacementItem::class,'return_item_id');
    }
    
}
