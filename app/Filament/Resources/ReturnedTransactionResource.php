<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnedTransactionResource\Pages;
use App\Models\{
    Branch,
    Employee,
    ReturnedTransaction,
    SaleTransaction,
    SaleTransactionItem,
    Stock
};
use Filament\Forms;
use Filament\Forms\Components\{
    DatePicker,
    Fieldset,
    Hidden,
    Select,
    Split,
    TextInput,
    Textarea,
    Toggle,
    Repeater,
    Section
};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class ReturnedTransactionResource extends Resource
{
    protected static ?string $model = ReturnedTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Returned Transactions';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Sales Transaction')->schema([
                Select::make('transaction_id')
                    ->label('Transaction #')
                    ->options(fn () => SaleTransaction::getOptionsArray(true))
                    ->allowHtml()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $transaction = SaleTransaction::find($state);
                        if ($transaction) {
                            $set('date_transaction', $transaction->date_paid);
                            $set('processed_by', $transaction->processed_by);
                            $set('branch_id', $transaction->branch_id);
                        }
                    })
                    ->placeholder('Select a transaction'),

                Split::make([
                    DatePicker::make('date_transaction')
                        ->label('Transaction Date')
                        ->disabled(),

                    Select::make('branch_id')
                        ->label('Branch')
                        ->options(Branch::getOptionsArray(false))
                        ->disabled()
                        ->dehydrated()
                        
                ])->visible(fn ($get) => $get('transaction_id')),

                Select::make('processed_by')
                    ->label('Processed By')
                    ->allowHtml()
                    ->options(Employee::getOptionsArray(false, false))
                    ->disabled()
                    ->visible(fn ($get) => $get('transaction_id')),
            ]),

            Repeater::make('return_item')
                ->label('Returned Items')
                ->disabled(fn ($get) => !$get('transaction_id'))
                ->schema([
                    Split::make([
                        Fieldset::make()->schema([
                            Select::make('returned_item')
                                ->label('Item to Return')
                                ->live()
                                ->allowHtml()
                                ->searchable()
                                ->options(fn($get)=>SaleTransactionItem::getOptionsArray($get('transaction_id'),html:true))
                                ->afterStateUpdated(function($state,$set){  
                                    $transactionItem = SaleTransactionItem::find($state);

                                    if($transactionItem){

                                        $soldPrice  = $transactionItem->original_price;
                                        $remainingQuantity = $transactionItem->quantity - $transactionItem->returned_quantity;
    
                                        $set('original_item_price',$soldPrice);
                                        $set('available_quantity',$remainingQuantity);
                                    }else{
                                        $set('original_item_price',null);
                                        $set('available_quantity',null);
                                    }
                                }),

                            Split::make([
                                TextInput::make('original_item_price')
                                    ->readOnly()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->label('Sold Price'),

                                TextInput::make('available_quantity')
                                    ->label('Available')
                                    ->inputMode('decimal')
                                    ->readOnly()
                                    ->numeric(),

                                TextInput::make('qty_returned')
                                    ->label('To Return')
                                    ->maxValue(fn($get)=>$get('available_quantity'))
                                    ->minValue(1)
                                    ->numeric()
                                    ->required(),

                            ])->columnSpanFull(),

                            Toggle::make('is_saleble')
                                ->label('Re-sellable'),

                            Textarea::make('issue')
                                ->label('Issue/Remarks')
                                ->required()
                                ->columnSpanFull(),
                        ]),

                        Fieldset::make()->schema([
                            Select::make('replacement_item_id')
                                ->label('Replacement Item')
                                ->allowHtml()
                                ->searchable()
                                ->live()
                                ->options(Stock::getOptionsArray())
                                ->afterStateUpdated(function($state,$set){
                                    $stock = Stock::find($state);

                                    if($stock){
                                        $retailPrice  = $stock->supply->price;
                                        $set('replacement_item_price',$retailPrice);
                                    }else{
                                        $set('replacement_item_price',null);
                                    }
                                }),
                                
                            Split::make([
                                TextInput::make('replacement_item_price')
                                    ->live()
                                    ->afterStateUpdated(function($get,$set){
                                        $totalAmount = $get('qty_replaced') * moneyToNumber($get('replacement_item_price'));
                                        $set('total_amount',$totalAmount);
                                    })
                                    ->label('Price'),

                                TextInput::make('qty_replaced')
                                    ->live()
                                    ->label('Quantity')
                                    ->afterStateUpdated(function($get,$set){
                                        $totalAmount = $get('qty_replaced') * moneyToNumber($get('replacement_item_price'));
                                        $set('total_amount',$totalAmount);
                                    })
                                    ->numeric(),
                                    
                                TextInput::make('total_amount')
                                    ->label('Total Amount')
                                    ->numeric(),

                            ])->columnSpanFull(),
                        ]),
                    ])
                ])
                ->deletable(false)
                ->reorderable(false)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (!auth()->user()->hasRole(['admin'])) {
                    $branchId = auth()->user()->employee->branch->branch->id;
                    return $query->where('branch_id', $branchId);
                }
            })
            ->columns([
                TextColumn::make('status')
                    ->formatStateUsing(strFormat()),

                TextColumn::make('id')
                    ->label('Return #')
                    ->formatStateUsing(fn ($state) => 'R-' . $state)
                    ->sortable(),

                TextColumn::make('returned_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('sale_transaction_id')
                    ->label('Transaction #')
                    ->formatStateUsing(fn ($state) => 'TX-' . $state)
                    ->sortable(),

                TextColumn::make('saleTransaction.customer.name')
                    ->label('Customer')
                    ->formatStateUsing(strFormat()),

                TextColumn::make('returnedItem.saleItem.supply.name')
                    ->label('Items Returned')
                    ->formatStateUsing(fn ($record, $state) =>
                        Str::headline($state) . ' (' . $record->quantity . '/qty)'
                    ),

                TextColumn::make('difference_value')
                    ->label('Required Payment')
                    ->money('PHP'),

                TextColumn::make('branch_id.name')
                    ->label('Branch')
                    ->formatStateUsing(strFormat()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnedTransactions::route('/'),
            'create' => Pages\CreateReturnedTransaction::route('/create'),
            'edit' => Pages\EditReturnedTransaction::route('/{record}/edit'),
        ];
    }
}
