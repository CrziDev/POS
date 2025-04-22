<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = [];

    public static function getOptionsArray(): array
    {
        $query = self::query();

        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> 
                        <b>Supplier:</b> " . $item->name . 
                    "</span>"
            ]
        )->all();
    }
}
