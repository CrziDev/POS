<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $guarded = [];

    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }
}
