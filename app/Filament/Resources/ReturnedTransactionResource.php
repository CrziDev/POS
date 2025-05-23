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

            // ...self::summarySection(),

            ...self::returnedItemSection()
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

    public static function summarySection(){
        return [
            Section::make('summary')
                ->make([
                    Split::make([
                        TextInput::make('total_returned')
                            ->label('Total Returned')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0)
                            ->readOnly(),
                        TextInput::make('total_replaced_item')
                            ->label('Total Replaced')
                            ->numeric()
                            ->default(0)
                            ->minValue(fn($get)=>$get('total_returned'))
                            ->readOnly(),
                    ])
                ]),
        ];
    }

    public static function returnedItemSection(){
        
        return [
            Repeater::make('return_item')
                ->visible(fn($operation,$get)=>$operation == 'create' && $get('sale_transaction_id'))
                ->label('Returned Items')
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
                                ->afterStateUpdated(function($set,$state,$get){
                                    $saleItem = SaleTransactionItem::find($state);

                                    if($saleItem){
                                        $set('returned_price',$saleItem->price_amount);
                                        $set('qty_returned',1);
                                    }else{
                                        $set('returned_price',0);
                                        $set('qty_returned',1);
                                    }
                                    self::recalculateSummary($get,$set);
                                })
                                ->columnSpanFull(),

                            TextInput::make('qty_returned')
                                ->label('Quantity')
                                ->minValue(1)
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(function($get,$set){
                                    self::recalculateSummary($get,$set);
                                })
                                ->required(),

                            Hidden::make('returned_price'),

                            Toggle::make('is_saleble')
                                ->label('Re-sellable')
                                ->extraAlpineAttributes(['class'=>'mt-1'])
                                ->inline(false),

                            Textarea::make('issue')
                                ->label('Issue/Remarks')
                                ->required()
                                ->columnSpanFull(),

                            ...self::summarySection(),

                        ]),

                        ...self::replacementItemSection()
                    ])
                ])
                ->deletable(false)
                ->reorderable(false)
                ->addActionLabel('Return More Item')
                ->columnSpanFull(),
                
        ];
    }


    public static function replacementItemSection(){
        return [

            Repeater::make('replacement_items')
                ->label('')
                ->reorderable(false)
                ->addActionLabel('Additional Replacement')
                ->schema([
                    Select::make('replacement_item_id')
                        ->label('Item Replacement')
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
                                $set('replacement_item_price',0);
                                $set('qty_replaced',0);
                                $set('total_amount',0);

                            }
                            self::recalculateSummary($get,$set,true);
                        }),
                        
                    Split::make([
                        TextInput::make('replacement_item_price')
                            ->live()
                            ->afterStateUpdated(function($get,$set){
                                if($get('qty_replaced') > 0){
                                        $totalAmount = $get('qty_replaced') * moneyToNumber($get('replacement_item_price'));
                                        $set('total_amount',$totalAmount);
                                    }
                                    self::recalculateSummary($get,$set,true);
                                })

                            ->required()
                            ->label('Price'),
    
                        TextInput::make('qty_replaced')
                            ->live()
                            ->label('Quantity')
                            ->minValue(1)
                            ->required()
                            ->afterStateUpdated(function($get,$set){
    
                                if($get('qty_replaced') > 0){
                                    $totalAmount = $get('qty_replaced') * moneyToNumber($get('replacement_item_price'));
                                    $set('total_amount',$totalAmount);
                                }

                                self::recalculateSummary($get,$set,true);
                            })
                            ->numeric(),
                            
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric(),
    
                    ])->columnSpanFull(),
                ]),


        ];
    }


    public static function recalculateSummary($get, $set, $inner = false)
    {
        $path = ($inner) ? '../../../../' : '../../';

        $returItemCollection = $get($path . 'return_item');

        foreach ($returItemCollection as $index => $item) {
            $totalReturned = moneyToNumber($item['returned_price']) * moneyToNumber($item['qty_returned']);

            $totalReplacement = 0;
            foreach ($item['replacement_items'] as $replacement) {
                $totalReplacement += $replacement['total_amount'];
            }

            $set($path . "return_item.{$index}.total_returned", number_format($totalReturned, 2));
            $set($path . "return_item.{$index}.total_replaced_item", number_format($totalReplacement, 2));
        }
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
