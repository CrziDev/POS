<?php

namespace App\Filament\Resources\ReturnedTransactionResoureResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnedItemRelationManager extends RelationManager
{
    protected static string $relationship = 'returnedItem';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('saleTransactionItem.supply.name')
                    ->label('Returned Item')
                    ->formatStateUsing(function ($record, $state) {
                        return Str::headline($state) . " ({$record->qty_returned} pcs)";
                    }),

                Tables\Columns\ToggleColumn::make('is_saleble')
                    ->label('Saleble')
                    ->disabled(),
                
                Tables\Columns\TextColumn::make('original_item_price')
                    ->label("Original Price")
                    ->money('PHP'),
                    
                Tables\Columns\TextColumn::make('replacementItems')
                    ->label('Replacement Items')
                    ->bulleted()
                    ->getStateUsing(function ($record) {
                        return $record->replacementItems
                            ->map(function ($item) {
                                $name = Str::headline($item->item->name);
                                $qty = $item->qty_replaced;
                                $price = number_format($item->replacement_item_price, 2); // Formats to 2 decimal places
                                return "{$name} ({$qty} pcs) - ₱{$price}";
                            })
                            ->toArray();
                    })
                    ->wrap(),


                Tables\Columns\TextColumn::make('value_difference')
                    ->label("Amount to Pay / Refund")
                    ->money('PHP'),
            ])
            ->filters([
            ])
            ->headerActions([
            ])  
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
