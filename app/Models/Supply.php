<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Milon\Barcode\Facades\DNS1DFacade;

class Supply extends Model
{
    use HasFactory;

    protected $guarded =[];

    public static function generateBarcode()
    {
        $supplies = self::all();

        $barCodes = $supplies->map(function($item){
            
            return [
                'content' => DNS1DFacade::getBarcodeHTML($item->sku, 'C128'),
                'label' => $item->sku,
            ];
        })->toArray();

        return $barCodes;
    }

    public static function getOptionsArray(): array
    {
        $query = self::query()->with('category');

        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> <b>Supply:</b> " . $item->name . "</span>". "<br>".
                    "<small>" .
                        "<span> Category:".$item->category?->name."<span>" .
                    "<small>" 
            ]
        )->all();
    }

    public function category(){
        return $this->belongsTo(SupplyCategory::class,'category_id');
    }

    public function unit(){
        return $this->belongsTo(SupplyUnit::class,'unit_id');
    }

}
