<?php

namespace App\Filament\Resources\ReturnedTransactionResource\Pages;

use App\Filament\Resources\ReturnedTransactionResource;
use App\Models\ReturnItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReturnedTransaction extends CreateRecord
{
    protected static string $resource = ReturnedTransactionResource::class;
    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {

         $mainPayload = [
                'sale_transaction_id' => $data['sale_transaction_id'],
                'returned_date'       => now(),
                'handled_by'          => auth()->user()->id,
                'status'              => 'pending',
                'branch_id'           => $data['branch_id'],
         ];

        $record = static::getModel()::create($mainPayload);

        $returnedItems = $data['return_item'];

        foreach($returnedItems as $item){
            $soldPrice =   moneyToNumber($item['original_item_price']);
            $replaceItemPrice = moneyToNumber($item['replacement_item_price']);

            $totalReplacementAmount = $replaceItemPrice * $item['qty_replaced'];
            $totalReturnedAmount = $soldPrice * $item['qty_returned'];

            if($totalReplacementAmount > $totalReturnedAmount ){
                $valueDifference = $totalReplacementAmount - $totalReturnedAmount;
            }else{
                $valueDifference = 0;
            }
            
            $itemPayload = [
                'returned_transaction_id'   => $record->id,
                'original_item_id'          => $item['returned_item'],         
                'replacement_item_id'       => $item['replacement_item_id'],         
                'qty_returned'              => $item['qty_returned'],          
                'qty_replaced'              => $item['qty_replaced'],         
                'original_item_price'       => $item['original_item_price'],         
                'replacement_item_price'    => $item['replacement_item_price'],         
                'value_difference'          => $valueDifference,         
                'issue'                     => $item['issue'],   
                'is_saleble'                => $item['is_saleble'],   
            ];

            ReturnItem::create($itemPayload);

        }

        return $record;
    }

     protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); 
    }
}
