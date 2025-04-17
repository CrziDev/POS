<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchEmployee extends Model
{
    protected $guarded = [];

    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id','id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
