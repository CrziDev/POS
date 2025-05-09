<?php

namespace App\Filament\Resources\SaleTransactionReturnResource\Pages;

use App\Filament\Resources\SaleTransactionReturnResource;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionReturnReplacement;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSaleTransactionReturn extends CreateRecord
{
    protected static string $resource = SaleTransactionReturnResource::class;

    protected function handleRecordCreation(array $data): Model
    {

        $saleTransaction = SaleTransaction::find($data['transaction_id']);
        $itemsToReturn = $data['returned_panel'];

        foreach($itemsToReturn as $toReturn){
            $transactionItem = $saleTransaction
                                ->items()
                                ->where('supply_id', $toReturn['returned_item'])
                                ->first();
            $mainPayload = [
                'sale_transaction_id' => $saleTransaction->id,
                'branch_id'           => $saleTransaction->branch_id,
                'returned_item_id'    => $toReturn['returned_item'],
                'quantity'            => $toReturn['return_quantity'],
                'remarks'             => $toReturn['remarks'],
                'issue_type'          => $toReturn['issue_type'],
                'handled_by'          => auth()->user()->employee->id,
                'price_sold'          => $transactionItem->price_amount ?? 0,
                'sale_transaction_item_id' => $transactionItem->id,

                'returned_at'         => now(),
            ];

            if($toReturn['issue_type'] == 'wrong_item'){
                $stock = Stock::where([
                    'branch_id' => $saleTransaction->branch_id,
                    'supply_id' => $toReturn['returned_item'],
                ])->first();
                
                if ($stock) {
                    $stock->increment('quantity', $toReturn['return_quantity']);
                }
            }

            $record = static::getModel()::create($mainPayload);

            $this->insertReturnedItems($record,$toReturn);

        }

        return $record;
    }

    protected function insertReturnedItems($record, $toReturn)
    {
        foreach ($toReturn['replacements'] as $item) {
            SaleTransactionReturnReplacement::create([
                'return_id'              => $record->id,
                'replacement_item_id'    => $item['replacement_item_id'],
                'replacement_item_id'    => $item['replacement_item_id'],
                'replacement_quantity'   => $item['replacement_quantity'],
            ]);

            $replacementStock = Stock::where([
                'branch_id' => $record->branch_id,
                'supply_id' => $item['replacement_item_id'],
            ])->first();

            if ($replacementStock) {
                $replacementStock->decrement('quantity', $item['replacement_quantity']);
            }
        }
    }
}
