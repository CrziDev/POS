<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Milon\Barcode\Facades\DNS1DFacade;

class Supply extends Model
{
    use HasFactory;

    protected $guarded =[];

    public static function booted():void
    {

        static::created(function ($model){
            
            Branch::all()->each(function($branch) use($model){
                Stock::firstOrCreate(
                    [
                        'branch_id'   => $branch->id,
                        'supply_id'  => $model->id,
                    ],
                    [
                        'quantity'      => 12,
                        'reorder_level' => 10,
                    ],
                );
            });

        });
    }

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

    public static function getOptionsArray($html = true,$showStock = false): array
    {
        $query = self::query()->with('category','stock');

        if(!$html){
            return $query->pluck('name','id')->toArray();
        }

        if($showStock){
            return $query->get()->mapWithKeys(fn($item) =>
                [
                    $item->id => 
                        "<span> <b>Supply:</b> " . $item->name . "</span>". "<br>".
                        "<small>" .
                            "<span> Branch: ".$item->stock->branch->name."<span>" . "<br>".
                            "<span> Stock: ".$item->stock->quantity."<span>" .
                        "<small>" 
                ]
            )->all();
        }


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

    public function stock(){
        return $this->hasOne(Stock::class,'supply_id');
    }

    public function category(){
        return $this->belongsTo(SupplyCategory::class,'category_id');
    }

    public function unit(){
        return $this->belongsTo(SupplyUnit::class,'unit_id');
    }

}
