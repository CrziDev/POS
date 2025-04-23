<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    
    protected $guarded =[];

    public function branchEmployees(){
        return $this->hasMany(BranchEmployee::class,'branch_id');
    }

    public static function getOptionsArray(): array
    {
        $query = self::query();

        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> <b>Name:</b> " . $item->name . "</span>". "<br>"
            ]
        )->all();
    }
}
