<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public static function getOptionsArray(): array
    {
        $query = self::query();

        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> 
                        <b>Name:</b> " . $item->name . 
                    "</span>"
            ]
        )->all();
    }
}
