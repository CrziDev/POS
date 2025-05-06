<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransaction extends Model
{
    protected $guarded = [];

    public function items(){
        return $this->hasMany(SaleTransactionItem::class,'sale_transaction_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class,'processed_by');
    }
}
