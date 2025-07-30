<?php

namespace App\Filament\Resources\ReturnedTransactionResource\Pages;

use App\Filament\Resources\ReturnedTransactionResource;
use App\Models\RelacementItem;
use App\Models\ReplacementItem;
use App\Models\ReturnItem;
use App\Traits\CreateActionLabel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateReturnedTransaction extends CreateRecord
{
    use CreateActionLabel;
    protected static string $resource = ReturnedTransactionResource::class;
    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {

         $mainPayload = [
            'sale_transaction_id' => $data['sale_transaction_id'],
            'returned_date'       => now(),
            'handled_by'          => auth_user()->id,
            'status'              => 'pending',
            'branch_id'           => $data['branch_id'],
         ];

        $record = static::getModel()::create($mainPayload);

        $returnedItems = $data['return_item'];

        foreach($returnedItems as $item){
            $soldPrice =   moneyToNumber($item['returned_price']);

            $totalReplacementAmount = self::getTotalReplacement($item);
            $totalReturnedAmount = $soldPrice * $item['qty_returned'];

            if($totalReplacementAmount > $totalReturnedAmount ){
                $valueDifference = $totalReplacementAmount - $totalReturnedAmount;
            }else{
                $valueDifference = 0;
            }
            
            $itemPayload = [
                'returned_transaction_id'   => $record->id,
                'original_item_id'          => $item['returned_item'],                  
                'qty_returned'              => $item['qty_returned'],         
                'original_item_price'       => $item['returned_price'],         
                'value_difference'          => $valueDifference,         
                'issue'                     => $item['issue'],   
                'is_saleble'                => $item['is_saleble'],   
            ];

            $returnedItems = ReturnItem::create($itemPayload);

            self::createReplacementRecord($returnedItems,$item);

        }

        return $record;
    }

    public function createReplacementRecord($returnedItemId,$item){

        foreach($item['replacement_items'] as $replacement){
            ReplacementItem::create([
                'return_item_id'         => $returnedItemId->id,
                'replacement_item_id'    => $replacement['replacement_item_id'],                  
                'qty_replaced'           => $replacement['qty_replaced'],         
                'replacement_item_price' => $replacement['replacement_item_price'],         
            ]);
        }
    }

    public function getTotalReplacement($item){
        $totalReplacement = 0;
        foreach($item['replacement_items'] as $replacement){
                $totalReplacement += $replacement['total_amount'];
        }

        return moneyToNumber($totalReplacement);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); 
    }
}
