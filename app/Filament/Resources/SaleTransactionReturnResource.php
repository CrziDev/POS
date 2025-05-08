<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleTransactionReturnResource\Pages;
use App\Filament\Resources\SaleTransactionReturnResource\RelationManagers;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionItem;
use App\Models\SaleTransactionReturn;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleTransactionReturnResource extends Resource
{
    protected static ?string $model = SaleTransactionReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public $returnedItem = [];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\grid::make(3)

                    ->schema([

                        Forms\Components\Section::make([

                            Forms\Components\Split::make([
                                
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->searchable()
                                    ->allowHtml()
                                    ->live()
                                    ->options(Customer::getOptionsArray(false))
                                    ->placeholder('Select a Customer')
                                    ->default(1),

                                Select::make('transaction_id')
                                    ->label('Transaction Number')
                                    ->options(fn($get)=>SaleTransaction::getOptionsArray(true,$get('customer_id')))
                                    ->allowHtml()
                                    ->live()
                                    ->afterStateUpdated(function($state,$set){
                                        $transaction = SaleTransaction::find($state);
                                        
                                        if($transaction){
                                            $set('date_transaction',$transaction->date_paid);
                                            $set('processed_by',$transaction->processed_by);
                                        }
                                    })
                                    ->default(1)
                                    ->placeholder(''),
                            ]),

                            DatePicker::make('date_transaction')
                                ->label('Transaction Date')
                                ->disabled(),

                            Select::make('processed_by')
                                ->label('Processed By')
                                ->allowHtml()
                                ->options(Employee::getOptionsArray(false,false))
                                ->disabled()

                        ])->columnSpan(function($get){
                            return $get('transaction_id')? 2 : 3;
                        }),

                        Forms\Components\Section::make([
                            CheckboxList::make('items_to_return')
                                ->label('Select items to return')
                                ->options(fn($get) => SaleTransactionItem::getOptionsArray($get('transaction_id')))
                                ->bulkToggleable()
                                ->live()
                                ->afterStateUpdated(function($state,$set,$get){
                                    $soldItems = SaleTransactionItem::whereIn('id',$state)->get();

                                    $itemToReturn = $soldItems->map(function($item){
                                        return [
                                            'returned_item'      => $item->supply_id,
                                            'original_quantity'  => $item->quantity,
                                            'return_quantity'    => null,
                                            'issue_type'         => null,
                                            'items'              => [],
                                            'remarks'            => null,

                                        ];
                                    })->toArray();

                                    $set('returned_panel',$itemToReturn);
                                })

                        ])
                        ->visible(fn($get)=>$get('transaction_id'))
                        ->columnSpan(1)
                ]),


                Forms\Components\Section::make([

                    Repeater::make('returned_panel')
                        ->label('Returned Items')
                        ->visible(fn($get)=>$get('items_to_return'))
                        ->schema([
                            Select::make('returned_item')
                                ->label('Item to Return')
                                ->options(SaleTransactionItem::getOptionsArray(false))
                                ->disabled(),

                            TextInput::make('original_quantity')
                                ->label('Original Qty')
                                ->disabled(),

                            TextInput::make('return_quantity')
                                ->label('Return Qty')
                                ->numeric()
                                ->required(),

                            Select::make('issue_type')
                                ->label('Issue Type')
                                ->options([
                                    'wrong_item' => 'Wrong Item',
                                    'defective' => 'Defective',
                                    'damaged' => 'Damaged',
                                ])
                                ->required(),

                            Repeater::make('items')
                                ->label('Replacement')
                                ->schema([

                                Select::make('replacement_item_id')
                                    ->label('Replacement Item')
                                    ->searchable()
                                    ->options(Stock::getOptionsArray())
                                    ->allowHtml()
                                    ->nullable(),
    
                                TextInput::make('replacement_quantity')
                                    ->label('Replacement Qty')
                                    ->numeric()
    
                                ])
                                ->addActionLabel('Add Item')
                                ->reorderable(false)
                                ->columns(2)
                                ->columnSpanFull(),
            
                            Textarea::make('remarks')
                                ->label('Remarks')
                                ->nullable()
                                ->hint('Optional')
                                ->columnSpanFull(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columns(2)
                        ->columnSpanFull()

                ])


                // Forms\Components\TextInput::make('sale_transaction_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('branch_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\DateTimePicker::make('returned_at'),
                // Forms\Components\Textarea::make('remarks')
                //     ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_transaction_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('returned_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSaleTransactionReturns::route('/'),
            'create' => Pages\CreateSaleTransactionReturn::route('/create'),
            'edit' => Pages\EditSaleTransactionReturn::route('/{record}/edit'),
        ];
    }
}
