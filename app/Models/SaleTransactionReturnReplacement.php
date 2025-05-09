<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransactionReturnReplacement extends Model
{
    protected $guarded = [];
    
    public function supply(){
        return $this->belongsTo(Supply::class,'replacement_item_id');
    }
}
