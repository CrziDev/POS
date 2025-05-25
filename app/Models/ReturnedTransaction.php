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
                    $stock->quantity += $item['qty_returned'];
                    $stock->save();
                }
            }

            self::processedReturnedItems($item);
            
            $saleTransactionItem->update([
                'returned_quantity' => $saleTransactionItem->returned_quantity + $item->qty_returned
            ]);

               Notification::make()
                ->title('Transaction Approved')
                ->success()
                ->send();
        });

    }

    public function processedReturnedItems($returnedItem){

         $returnedItem->replacementItems()->each(function($replacement){

            $replacementItem = Stock::where('supply_id', $replacement->replacement_item_id)
                ->where('branch_id', $this->branch_id)
                ->first();
    
            if ($replacementItem) {
                $replacementItem->quantity -= $replacement['qty_replaced'];
                $replacementItem->save();
            }
         });
         
    }

    public function recordPayment($data){
        $this->replacementPayment()->create([
            'returned_transaction_id' => $this->id,
            'amount_paid'             => $data['amount_paid'],
            'payment_method'          => $data['payment_method'],
            'date_paid'               => $data['date_paid'],
            'reference_no'            => $data['reference_no'] ?? null,
            'received_by'             => auth_user()->id,
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
