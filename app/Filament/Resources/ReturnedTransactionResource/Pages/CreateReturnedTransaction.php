<?php

namespace App\Filament\Resources\ReturnedTransactionResource\Pages;

use App\Filament\Resources\ReturnedTransactionResource;
use App\Models\ReturnItem;
use App\Models\SaleTransactionItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReturnedTransaction extends CreateRecord
{
    protected static string $resource = ReturnedTransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {

         $mainPayload = [
                'sale_transaction_id' => $data['transaction_id'],
                'quantity'            => $data['date_transaction'],
                'processed_by'        => $data['processed_by'],
                'status'              => 'pending',
                'remarks'             => $data['branch_id'],
         ];

        $record = static::getModel()::create($mainPayload);

        $returnedItems = $data['return_item'];

        foreach($returnedItems as $item){
            $soldPrice =   moneyToNumber($item['original_item_price']);
            $replaceItemPrice = moneyToNumber($item['replacement_item_price']);

            $itemPayload = [
                'returned_transaction_id'   => $record->id,
                'original_item_id'          => $item['returned_item'],         
                'replacement_item_id'       => $item['replacement_item_id'],         
                'qty_returned'              => $item['qty_returned'],          
                'qty_replaced'              => $item['qty_replaced'],         
                'original_item_price'       => $item['original_item_price'],         
                'replacement_item_price'    => $item['replacement_item_price'],         
                'value_difference'          => $soldPrice - $replaceItemPrice,         
                'issue'                     => $item['issue'],         
            ];

            ReturnItem::create($itemPayload);

        }

        return $record;
    }
}
