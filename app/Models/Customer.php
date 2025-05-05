<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    public static function getOptionsArray($html = true): array
    {
        $query = self::query();


        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> <b>Name:</b> " . $item->name . "</span>". "<br>" .
                    '<small>' .
                        "<span> <b>Contact Number:</b> " . $item->contact_no . "</span>". "<br>" .
                        "<span> <b>Addres:</b> " . $item->address . "</span>". "<br>" .
                    '<small>' 
            ]
        )->all();
    }
}
