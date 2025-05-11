<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnedTransactionResource\Pages;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ReturnedTransaction;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionItem;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
        return $form
            ->schema([

                Forms\Components\Section::make('Sales Transaction')->schema([
                    Select::make('transaction_id')
                        ->label('Transaction #')
                        ->options(fn ($get) => SaleTransaction::getOptionsArray(true))
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

                    Forms\Components\Split::make([

                        DatePicker::make('date_transaction')
                            ->label('Transaction Date')
                            ->disabled(),
                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::getOptionsArray(false))
                            ->dehydrated()
                            ->disabled()
                    ])
                    ->visible(fn($get)=>$get('transaction_id')),

                    Forms\Components\Select::make('processed_by')
                        ->label('Processed By')
                        ->allowHtml()
                        ->options(Employee::getOptionsArray(false, false))
                        ->disabled()
                        ->visible(fn($get)=>$get('transaction_id')),
                ]),

                Forms\Components\Repeater::make('return_item')
                    ->label('Returned Items')
                    ->schema([
                        Split::make([
                            Fieldset::make('')->schema([    
                                Split::make([
                                    Forms\Components\Select::make('returned_item')
                                        ->label('Item to Return')
                                        ->options(SaleTransactionItem::getOptionsArray()),
                                ])
                                ->columnSpanFull(),
                                Split::make([
                                    Forms\Components\TextInput::make('original_item_price')
                                        ->label('Sold Price'),
                                        
                                    Forms\Components\TextInput::make('available_quantity')
                                        ->label('Available')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('qty_returned')
                                        ->label('To Return')
                                        ->numeric()
                                        ->required()
                                        ->maxValue(fn ($get) => $get('original_quantity')),

                                ])
                                ->columnSpanFull(),
                                Forms\Components\Textarea::make('issue')
                                    ->label('Issue/Remarks')
                                    ->required()
                                    ->columnSpanFull(),

                            ]),

                        Fieldset::make('')->schema([    
                            Split::make([
                               Forms\Components\Select::make('replacement_item_id')
                                   ->label('Replacement Item')
                                   ->options(Stock::getOptionsArray()),
                                   
                                Forms\Components\TextInput::make('replacement_item_price')
                                    ->label('Price'),

                               Forms\Components\TextInput::make('qty_replaced')
                                   ->label('Quantity')
                                   ->numeric()
                            ])
                            ->columnSpanFull()
                        ]),
                                
                
                        ]),
                    

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
                    return $query->where('branch_id', auth()->user()->employee->branch->branch->id); 
                } 
            }) 
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(strFormat()),

                Tables\Columns\TextColumn::make('id')
                    ->label('Return #')
                    ->formatStateUsing(fn ($state) => 'R-' . $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('returned_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_transaction_id')
                    ->label('Transaction #')
                    ->formatStateUsing(fn ($state) => 'TX-' . $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('saleTransaction.customer.name')
                    ->label('Customer')
                    ->formatStateUsing(strFormat()),

                Tables\Columns\TextColumn::make('returnedItem.saleItem.supply.name')
                    ->label('Items Returned')
                    ->formatStateUsing(fn($record,$state)=>Str::headline($state) .' ('.$record->quantity.'/qty)'),
                Tables\Columns\TextColumn::make('difference_value')
                    ->label('Required Payment')
                    ->money('PHP'),
                Tables\Columns\TextColumn::make('branch_id.name')
                    ->label('Branch')
                    ->formatStateUsing(strFormat()),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
