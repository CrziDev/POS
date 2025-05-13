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

     public function approveReturn(){
        $this->status = 'approved';
        $this->update();

        $this->returnedItem()->each(function($item){
            $saleTransactionItem = $item->saleTransactionItem;
            $saleTransactionItem->update([
                'returned_quantity' => $saleTransactionItem->returned_quantity + $item->qty_returned
            ]);
        });

    }

    public function recordPayment($data){
        $this->replacementPayment()->create([
            'returned_transaction_id' => $this->id,
            'amount'            => $data['amount'],
            'payment_method'    => $data['payment_method'],
            'reference_no'      => $data['reference_no'],

            'received_by'       => auth()->user()->id,
        ]);
    }

    public function replacementPayment(){
        return $this->belongsTo(ReplacementPayment::class,'replacement_item_id');
    }

    public function handleBy(){
        return $this->belongsTo(Employee::class,'handled_by');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function saleTransaction(){
        return $this->belongsTo(SaleTransaction::class,'sale_transaction_id');
    }

    public function returnedItem(){
        return $this->hasMany(ReturnItem::class,'returned_transaction_id');
    }

}
