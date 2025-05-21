<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnedTransactionResource\Pages;
use App\Filament\Resources\ReturnedTransactionResoureResource\RelationManagers\ReturnedItemRelationManager;
use App\Models\{
    Branch,
    Customer,
    Employee,
    ReturnedTransaction,
    SaleTransaction,
    SaleTransactionItem,
    Stock
};
use DateTime;
use Filament\Forms\Components\{
    DatePicker,
    Fieldset,
    Hidden,
    Placeholder,
    Select,
    Split,
    TextInput,
    Textarea,
    Toggle,
    Repeater,
    Section
};
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
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

    public static function getNavigationBadge(): ?string
    {
        return ReturnedTransaction::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }


    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Sales Transaction')->schema([
                Select::make('sale_transaction_id')
                    ->label('Transaction #')
                    ->options(fn () => SaleTransaction::getOptionsArray(html:true,paid:true))
                    ->searchable()
                    ->helperText('Only Paid Transaction Can be selected')
                    ->allowHtml()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $transaction = SaleTransaction::find($state);
                        if ($transaction) {
                            $set('date_transaction', $transaction->transaction_date);
                            $set('handled_by', $transaction->processed_by);
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
                        
                ])->visible(fn ($get) => $get('sale_transaction_id')),

                Select::make('handled_by')
                    ->label('Handled By')
                    ->allowHtml()
                    ->options(Employee::getOptionsArray(false, false))
                    ->disabled()
                    ->visible(fn ($get) => $get('sale_transaction_id')),
            ]),

            Repeater::make('return_item')
                ->visible(fn($operation,$get)=>$operation == 'create' && $get('sale_transaction_id'))
                ->label('Returned Items')
                // ->afterStateHydrated(fn($set)=>$set('return_item',[]))
                ->schema([
                    Split::make([
                        Fieldset::make()->schema([
                            Select::make('returned_item')
                                ->required()
                                ->label('Item to Return')
                                ->live()
                                ->allowHtml()
                                ->searchable()
                                ->options(fn($get)=>SaleTransactionItem::getOptionsArray($get('../../sale_transaction_id'),html:true))
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
                                ->afterStateUpdated(function($state,$set,$get){
                                    $stock = Stock::find($state);

                                    if($stock){
                                        $retailPrice  = $stock->supply->price;
                                        $set('replacement_item_price',$retailPrice);
                                        $set('qty_replaced',1);

                                        if($get('qty_replaced') > 0){
                                            $totalAmount = $get('qty_replaced') * moneyToNumber($get('replacement_item_price'));
                                            $set('total_amount',$totalAmount);
                                        }
                                    }else{
                                        $set('replacement_item_price',null);
                                        $set('qty_replaced',null);

                                    }
                                }),
                                
                            Split::make([
                                TextInput::make('replacement_item_price')
                                    ->live()
                                    ->afterStateUpdated(function($get,$set){
                                      if($get('qty_replaced') > 0){
                                            $totalAmount = $get('qty_replaced') * moneyToNumber($get('replacement_item_price'));
                                            $set('total_amount',$totalAmount);
                                        }
                                    })
                                    ->label('Price'),

                                TextInput::make('qty_replaced')
                                    ->live()
                                    ->label('Quantity')
                                    ->minValue(1)
                                    ->afterStateUpdated(function($get,$set){

                                        if($get('qty_replaced') > 0){
                                            $totalAmount = $get('qty_replaced') * moneyToNumber($get('replacement_item_price'));
                                            $set('total_amount',$totalAmount);
                                        }
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
                ->addActionLabel('Return Item')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->modifyQueryUsing(function (Builder $query) {
                if (auth_user()->hasRole(['admin','super-admin'])) {
                    return $query;
                }else{
                    return $query->whereIn('branch_id', auth_user()->employee->branch()->pluck('branch_id'));
                }
            })
            ->columns([
               TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(strFormat())
                ->color(fn ($state) => match (Str::lower($state)) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'declined', 'rejected' => 'danger',
                    'completed' => 'primary',
                    default => 'gray',
                }),
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
                TextColumn::make('returnedItem.saleTransactionItem.supply.name')
                    ->label('Items Returned')
                    ->bulleted()
                    ->formatStateUsing(strFormat()),

                TextColumn::make('difference_value')
                    ->label('Amount to Pay')
                    ->default(function($record){    
                        $payable = $record->returnedItem->sum('value_difference');

                        if($payable == 0){
                            return 'N/A';
                        }

                        return $payable;
                    })
                    ->color(fn ($record) => 
                        ($record->returnedItem->sum('value_difference') > 0 && $record->status != 'approved') ? 'danger' : 'gray'
                    )
                    ->money('PHP'),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->formatStateUsing(strFormat()),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->formatStateUsing(strFormat())
                    ->toggleable(isToggledHiddenByDefault:true),
                TextColumn::make('handleBy.full_name')
                    ->label('Handled By')
                    ->formatStateUsing(strFormat())
                    ->toggleable(isToggledHiddenByDefault:true),
            ])
            ->recordAction(false)
            ->recordUrl(null)
            ->actions([
                Tables\Actions\Action::make('process-entry')
                    ->label('Process')
                    ->disabled(fn($record)=>$record->status == 'approved')
                    ->form(function($record){

                        if($record->returnedItem->sum('value_difference') > 0){
                            return [
                                Section::make('Additional Payment')->schema([
                                    // Select::make('customer')
                                    //     ->placeholder('Select Customer')
                                    //     ->createOptionForm([
                                    //         Section::make('New Customer')->schema([
                                    //             TextInput::make('name')
                                    //                 ->label('Customer Name')
                                    //                 ->required(),
                                    //             TextInput::make('contact_number')
                                    //                 ->label('Contact Number'),
                                    //             TextInput::make('address')
                                    //                 ->label('Address'),
                                    //         ]),
                                    //     ])
                                    //     ->createOptionUsing(function (array $data): int {
                                    //         $customer = Customer::create($data);
                                    //         return $customer->getKey();
                                    //     })
                                    //     ->required()
                                    //     ->searchable()
                                    //     ->allowHtml()
                                    //     ->options(Customer::getOptionsArray()),

                                    Select::make('payment_method')
                                        ->options([
                                            'g-cash' => 'G-Cash',
                                            'cash'  => 'Cash',
                                        ])
                                        ->required()
                                        ->default('g-cash')
                                        ->live(),

                                    DatePicker::make('date_paid')
                                        ->required(),

                                    Split::make([
                                        TextInput::make('reference_no')
                                            ->visible(fn ($get) => $get('payment_method') === 'g-cash')
                                            ->label('Reference No.')
                                            ->required(),
                                        TextInput::make('amount_paid')
                                            ->label('Amount')
                                            ->afterStateHydrated(function ($record,$set) {
                                                $set('amount_paid',$record->returnedItem->sum('value_difference'));
                                            })
                                            ->disabled()
                                            ->dehydrated()
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->numeric()
                                            ->minValue(1)
                                            ->inputMode('decimal')
                                            ->required(),
                                    ]),
                                ]),
                            ];
                        }

                        return [
                            Placeholder::make('No Payment Required')
                        ];
                    })
                    ->action(function($record,$data){

                        if($data){
                            $record->recordPayment($data);
                        };
                        $record->approveReturn();


                    })
                    ->modalSubmitActionLabel('Approved')
                    ->icon('heroicon-o-clipboard-document-check'),
                Tables\Actions\ViewAction::make()


            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReturnedItemRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnedTransactions::route('/'),
            'create' => Pages\CreateReturnedTransaction::route('/create'),
            'view' => Pages\ViewReturnedTransaction::route('/{record}/view'),
        ];
    }
}
