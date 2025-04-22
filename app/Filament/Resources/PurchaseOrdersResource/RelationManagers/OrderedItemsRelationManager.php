<?php

namespace App\Filament\Resources\PurchaseOrdersResource\RelationManagers;

use App\Models\Supplier;
use App\Models\Supply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class OrderedItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderedItems';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('supply_id')
                ->label('Supply Item')
                ->required()
                ->searchable()
                ->allowHtml()
                ->columnSpanFull()
                ->options(Supply::getOptionsArray()),

            Forms\Components\Select::make('supplier_id')
                ->label('Supplier')
                ->required()
                ->searchable()
                ->allowHtml()
                ->columnSpanFull()
                ->options(Supplier::getOptionsArray()),

            Forms\Components\Toggle::make('is_price_set')
                ->label('Specify product price at delivery'),

            Forms\Components\TextInput::make('quantity')
                ->label('Quantity')
                ->required()
                ->numeric()
                ->live()
                ->afterStateUpdated(function ($get, $set) {
                    $totalAmount = moneyToNumber($get('quantity')) * moneyToNumber($get('price'));
                    $set('total_amount', number_format($totalAmount, 2));
                }),

            Forms\Components\TextInput::make('price')
                ->label('Item Price')
                ->hint('Specify the product price')
                ->mask(RawJs::make('$money($input)'))
                ->stripCharacters(',')
                ->minValue(1)
                ->numeric()
                ->live()
                ->inputMode('decimal')
                ->afterStateUpdated(function ($get, $set) {
                    $totalAmount = moneyToNumber($get('quantity')) * moneyToNumber($get('price'));
                    $set('total_amount', number_format($totalAmount, 2));
                }),

            Forms\Components\TextInput::make('total_amount')
                ->label('Total Amount')
                ->stripCharacters(',')
                ->minValue(1)
                ->required()
                ->disabled(), 
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supply.name')
                    ->label('Supply'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('PHP', true),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PHP', true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Item')
                    ->color('info'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
