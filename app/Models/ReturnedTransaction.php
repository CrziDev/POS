<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnedTransaction extends Model
{
    protected $fillable = [
        'id',
        'sale_transaction_id',
        'branch_id',
        'returned_date',
        'handled_by',
        'status'
    ];

    public const ISSUE_TYPES = [
        'wrong_item' => 'Wrong Item',
        'defective' => 'Defective',
        'damaged' => 'Damaged',
    ];
    
    // public function customer(){
    //     return $this->belongsTo(Customer::class,'customer_id');
    // }

    // public function saleTransaction(){
    //     return $this->belongsTo(SaleTransaction::class,'sale_transaction_id');
    // }

    // public function returnedItem(){
    //     return $this->belongsTo(Supply::class,'returned_item_id');
    // }

    // public function replacements(){
    //     return $this->hasMany(SaleTransactionReturnReplacement::class,'return_id');
    // }
}
