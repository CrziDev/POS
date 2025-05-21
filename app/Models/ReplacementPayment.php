<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplacementPayment extends Model
{
    protected $guarded = [];

    public function processedBy(){
        return $this->belongsTo(Employee::class,'received_by');
    }

    public function returnedTransaction(){
        return $this->belongsTo(ReturnedTransaction::class,'returned_transaction_id');
    }
}
