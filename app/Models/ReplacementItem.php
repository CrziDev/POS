<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplacementItem extends Model
{
    protected $guarded = [];

    public function item(){
        return $this->belongsTo(Supply::class,'replacement_item_id');
    }
}
