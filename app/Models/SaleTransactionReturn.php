<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransactionReturn extends Model
{
    protected $fillable = [
        'id',
        'sale_transaction_id',
        'branch_id',
        'returned_at',
        'handled_by',
        'quantity',
        'returned_item_id',
        'issue_type',
        'price_sold',
        'sale_transaction_item_id',
        'remarks'
    ];

    public const ISSUE_TYPES = [
        'wrong_item' => 'Wrong Item',
        'defective' => 'Defective',
        'damaged' => 'Damaged',
    ];
    
    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function saleTransaction(){
        return $this->belongsTo(SaleTransaction::class,'sale_transaction_id');
    }

    public function returnedItem(){
        return $this->belongsTo(Supply::class,'returned_item_id');
    }

    public function replacements(){
        return $this->hasMany(SaleTransactionReturnReplacement::class,'return_id');
    }
}
