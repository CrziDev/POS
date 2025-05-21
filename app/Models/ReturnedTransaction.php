<?php

namespace App\Models;

use Filament\Notifications\Notification;
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

            
            if($item->is_saleble){
                $stock = Stock::where('supply_id', $saleTransactionItem->supply_id)
                    ->where('branch_id', $this->branch_id)
                    ->first();
        
                if ($stock) {
                    $stock->quantity += $item['qty'];
                    $stock->save();
                }
            }

              $returnedItem = Stock::where('supply_id', $item->replacement_item_price)
                    ->where('branch_id', $this->branch_id)
                    ->first();
        
                if ($returnedItem) {
                    $returnedItem->quantity -= $item['qty'];
                    $returnedItem->save();
                }
            
            $saleTransactionItem->update([
                'returned_quantity' => $saleTransactionItem->returned_quantity + $item->qty_returned
            ]);

               Notification::make()
                ->title('Transaction Approved')
                ->success()
                ->send();
        });

    }

    public function recordPayment($data){
        $this->replacementPayment()->create([
            'returned_transaction_id' => $this->id,
            'amount_paid'            => $data['amount_paid'],
            'payment_method'    => $data['payment_method'],
            'date_paid'         => $data['date_paid'],
            'reference_no'      => $data['reference_no'],
            'received_by'       => auth()->user()->id,
        ]);

        Notification::make()
            ->title('Payment Was Recorded')
            ->success()
            ->send();
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
