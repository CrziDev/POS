<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $guarded = [];

    public function processedBy(){
        return $this->belongsTo(Employee::class,'processed_by');
    }
}
