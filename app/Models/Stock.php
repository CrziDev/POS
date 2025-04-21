<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $guarded = [];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function supply(){
        return $this->belongsTo(Supply::class,'supply_id');
    }
}
