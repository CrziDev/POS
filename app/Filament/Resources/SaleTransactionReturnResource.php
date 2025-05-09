<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleTransactionReturnResource\Pages;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionItem;
use App\Models\SaleTransactionReturn;
use App\Models\Stock;
use App\Models\Supply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SaleTransactionReturnResource extends Resource
{
    protected static ?string $model = SaleTransactionReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Returns';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([

                    Forms\Components\Section::make([
                        Forms\Components\Split::make([
                            Forms\Components\Select::make('customer_id')
                                ->label('Customer')
                                ->searchable()
                                ->allowHtml()
                                ->live()
                                ->options(Customer::getOptionsArray(false))
                                ->placeholder('Select a customer'),

                            Forms\Components\Select::make('transaction_id')
                                ->label('Transaction #')
                                ->options(fn ($get) => SaleTransaction::getOptionsArray(true, $get('customer_id')))
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
                        ]),

                        Forms\Components\Split::make([
                            Forms\Components\DatePicker::make('date_transaction')
                                ->label('Transaction Date')
                                ->disabled(),
                            Forms\Components\Select::make('branch_id')
                                ->label('Branch')
                                ->options(Branch::getOptionsArray(false))
                                ->dehydrated()
                                ->disabled()
                        ]),

                        Forms\Components\Select::make('processed_by')
                            ->label('Processed By')
                            ->allowHtml()
                            ->options(Employee::getOptionsArray(false, false))
                            ->disabled(),
                    ])->columnSpan(fn ($get) => $get('transaction_id') ? 2 : 3),

                    Forms\Components\Section::make([
                        Forms\Components\CheckboxList::make('items_to_return')
                            ->label('Select items to return')
                            ->options(fn ($get) => SaleTransactionItem::getOptionsArray($get('transaction_id')))
                            ->bulkToggleable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set,$get) {
                                $set('returned_panel', self::mapItemsForReturn($state,$get('transaction_id')));
                            }),
                    ])
                        ->visible(fn ($get) => $get('transaction_id'))
                        ->columnSpan(1),
                ]),

                Forms\Components\Section::make([
                    Forms\Components\Repeater::make('returned_panel')
                        ->label('Returned Items')
                        ->visible(fn ($get) => $get('items_to_return'))
                        ->schema([
                            Forms\Components\Select::make('returned_item')
                                ->label('Item to Return')
                                ->options(Supply::getOptionsArray(false))
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('original_quantity')
                                ->label('Original Qty')
                                ->disabled()
                                ->numeric()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('return_quantity')
                                ->label('Return Qty')
                                ->numeric()
                                ->required()
                                ->helperText('Must not exceed original quantity')
                                ->maxValue(fn ($get) => $get('original_quantity')),

                            Forms\Components\Select::make('issue_type')
                                ->label('Issue Type')
                                ->required()
                                ->options(SaleTransactionReturn::ISSUE_TYPES),

                            Forms\Components\Repeater::make('replacements')
                                ->label('Replacements')
                                ->schema([
                                    Forms\Components\Select::make('replacement_item_id')
                                        ->label('Replacement Item')
                                        ->searchable()
                                        ->options(Supply::getOptionsArray(true,showStock:true))
                                        ->allowHtml()
                                        ->live()
                                        ->nullable(),

                                    Forms\Components\TextInput::make('replacement_quantity')
                                        ->label('Replacement Qty')
                                        ->minValue(0)
                                        ->maxValue(function($get){
                                            $branchId = $get('../../../../branch_id'); 
                                            $supplyId = $get('replacement_item_id');

                                            if ($branchId && $supplyId) {
                                                $stock = Stock::where([
                                                    'branch_id' => $branchId,
                                                    'supply_id' => $supplyId,
                                                ])->first();

                                                return $stock?->quantity ?? 0;
                                            }

                                            return 0;
                                        })
                                        ->numeric(),
                                ])
                                ->addActionLabel('Add Replacement')
                                ->reorderable(false)
                                ->columns(2)
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('remarks')
                                ->label('Remarks')
                                ->hint('Optional')
                                ->nullable()
                                ->columnSpanFull(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
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
                Tables\Columns\TextColumn::make('id')
                    ->label('Return #')
                    ->formatStateUsing(fn ($state) => 'R-' . $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('returned_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_transaction_id')
                    ->label('Transaction #')
                    ->formatStateUsing(fn ($state) => 'TX-' . $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('saleTransaction.customer.name')
                    ->label('Customer')
                    ->formatStateUsing(strFormat()),

                Tables\Columns\TextColumn::make('returnedItem.name')
                    ->label('Item Returned')
                    ->formatStateUsing(fn($record,$state)=>Str::headline($state) .' ('.$record->quantity.'/qty)'),

                Tables\Columns\TextColumn::make('issue_type')
                    ->label('Issue')
                    ->formatStateUsing(strFormat()),

                Tables\Columns\TextColumn::make('replacements')
                    ->label('Replacements')
                    ->formatStateUsing(function ($state, $record) {
                        return collect($record->replacements)
                            ->map(function ($replacement) {
                                $name = $replacement->supply?->name ?? 'N/A';
                                $qty = $replacement->replacement_quantity ?? 0;
                                return "$name ($qty/qty)";
                            })
                            ->implode("\n");
                    })
                    ->bulleted()
                    ->limit(50),
                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('saleTransaction.customer', 'name')
                    ->searchable(),
    
                Tables\Filters\Filter::make('returned_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('returned_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('returned_at', '<=', $data['until']));
                    }),
    
                Tables\Filters\SelectFilter::make('issue_type')
                    ->label('Issue Type')
                    ->options(SaleTransactionReturn::ISSUE_TYPES),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSaleTransactionReturns::route('/'),
            'create' => Pages\CreateSaleTransactionReturn::route('/create'),
            // 'edit' => Pages\EditSaleTransactionReturn::route('/{record}/edit'),
        ];
    }

    protected static function mapItemsForReturn(array $itemIds,$transactionId): array
    {
        return SaleTransactionItem::whereIn('id', $itemIds)->get()->map(function ($item) use($transactionId){
            
            $returnedItem = SaleTransactionReturn::where([
                'sale_transaction_id' => $transactionId,
                'returned_item_id' => $item->supply_id,
            ])->sum('quantity');
            
            return [
                'returned_item' => $item->supply_id,
                'original_quantity' => $item->quantity - $returnedItem ?? 0,
                'return_quantity' => null,
                'issue_type' => null,
                'remarks' => null,
            ];
        })->toArray();
    }
}
