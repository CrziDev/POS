<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransaction extends Model
{
    protected $guarded = [];

    public function items(){
        return $this->hasMany(SaleTransactionItem::class,'sale_transaction_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class,'processed_by');
    }

    public static function getOptionsArray($html = true,$customer = false,$paid = false): array
    {
        $query = self::query()->when($paid,fn($q)=>$q->where('status','paid'));

        if(!auth_user()->hasRole(['owner','super-admin'])){
            $query = $query->where('branch_id',auth_user()->employee->branch()->first()->branch_id);
        }

        if(!$html){
            return $query->pluck('id','id')->toArray();
        }

        if($customer){
            $query = $query->where('customer_id',$customer);
        }

        return $query->get()->mapWithKeys(fn($item) =>
            [
                $item->id => 
                    "<span> <b>TX - </b> " . $item->id . "</span>". "<br>" 
            ]
        )->all();
    }
}
