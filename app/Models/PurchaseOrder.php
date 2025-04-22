<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $guarded = [];

    public function orderedItems(){
        return $this->hasMany(PurchaseOrderItem::class,'purchase_order_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    
    public function preparedBy(){
        return $this->belongsTo(User::class,'prepared_by');
    }

}
