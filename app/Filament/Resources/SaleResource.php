<?php

namespace App\Filament\Resources;

use App\Actions\CreateSalePayment;
use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\Pages\CreateSale;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Branch;
use App\Models\SaleTransaction;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateRangeFilter;

class SaleResource extends Resource
{
    protected static ?string $model = SaleTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Sales Transaction';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {

          if (auth_user()->hasRole(['owner','super-admin'])) {
                return SaleTransaction::where('status', 'pending')->count();
            }else{
                return SaleTransaction::where('status', 'pending')
                        ->whereIn('branch_id', auth_user()->employee->branch()->pluck('branch_id'))->count();
            }
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Customer & Payment Info')
                ->schema([
                    TextInput::make('id')
                        ->label('Transaction No.')
                        ->formatStateUsing(fn($state) => 'TX - ' . $state)
                        ->nullable()
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('transaction_date')
                        ->label('Transaction Date')
                        ->required(),

                    Select::make('status')
                        ->label('Transaction Status')
                        ->options([
                            'pending' => 'Pending',
                            'voided' => 'Voided',
                            'paid' => 'Paid',
                        ])
                        ->required(),
                ])
                ->columns(2),

            Section::make('Transaction Details')
                ->schema([
                    TextInput::make('discount_value')
                        ->label('Discount')
                        ->numeric()
                        ->default(0),

                    TextInput::make('total_amount')
                        ->label('Total Amount')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(function (Builder $query) {
            if (auth_user()->hasRole(['owner','super-admin'])) {
                return $query;
            } else {
                return $query->whereIn('branch_id', auth_user()->employee->branch()->pluck('branch_id'));
            }
        })
        ->columns([
            Tables\Columns\TextColumn::make('status')
                ->formatStateUsing(strFormat())
                ->badge()
                ->colors([
                    'gray' => 'pending',
                    'danger' => 'voided',
                    'success' => 'paid',
                ]),
            Tables\Columns\TextColumn::make('id')
                ->label('Transaction No.')
                ->sortable()
                ->formatStateUsing(fn($state) => 'TX - ' . $state),
            Tables\Columns\TextColumn::make('transaction_date')
                ->label('Date Transaction')
                ->date(),
            Tables\Columns\TextColumn::make('employee.fullName')
                ->label('Processed By')
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->whereHas('employee', function (Builder $sq) use ($search) {
                        $sq->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    });
                })
                ->sortable(),
            Tables\Columns\TextColumn::make('branch.name')
                ->label('Branch')
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('total_amount')
                ->badge()
                ->money('PHP')
                ->sortable(),
            Tables\Columns\TextColumn::make('discount_value')
                ->badge()
                ->color('warning')
                ->label('Discount')
                ->money('PHP'),
             Tables\Columns\TextColumn::make('remarks')
                ->label('Remarks')
                ->placeholder('None')
                ->sortable(),
        ])
        ->filters([
            SelectFilter::make('payment_method')
                ->placeholder('All Method')
                ->options([
                    'Cash' => 'Cash',
                    'g-cash' => 'GCash',
                ])
                ->label('Payment Method'),

            SelectFilter::make('status')
                ->placeholder('All Status')
                ->options([
                    'Paid' => 'Paid',
                    'Voided' => 'Voided',
                ]),

            SelectFilter::make('branch')
                ->placeholder('All Branch')
                ->options(Branch::getOptionsArray(false)),
        ])
        ->recordAction(false)
        ->recordUrl(null)
        ->actions([
            Tables\Actions\Action::make('payment')
                ->icon('heroicon-m-credit-card')
                ->label('Pay')
                ->form(fn ($record) => self::paymentForm($record))
                ->modalHeading('Record Payment')
                ->modalSubmitActionLabel('Submit Payment')
                ->disabled(fn($record) => $record->status == 'voided' || $record->status == 'paid')
                ->color('success')
                ->mountUsing(function($action, $form, $record) {
                    if (!auth_user()->hasRole(['cashier'])) {
                        Notification::make()
                            ->title('Payment Processing are only allowed for Cashier')
                            ->warning()
                            ->send();
                        $action->cancel();
                    }

                    $form->fill($record->toArray());
                })
                ->action(function(CreateSalePayment $createSalePayment, $record, $data) {
                    $createSalePayment->handle(
                        $record->id,
                        auth_user()->id,
                        $record->branch_id,
                        $data['payment_method'],
                        $data['reference_number'] ?? null,
                        $data['total_amount'],
                    );

                    $record->update([
                        'status' => 'paid'
                    ]);

                    Notification::make()
                        ->title('Payment successfully Recorded')
                        ->success()
                        ->send();
                }),

            Tables\Actions\Action::make('void')
                ->icon('heroicon-m-x-circle')
                ->label('Void')
                ->requiresConfirmation()
                ->modalHeading('Void Transaction')
                ->modalDescription('Are you sure you want to void this transaction? This action cannot be undone.')
                ->modalSubmitActionLabel('Confirm Void')
                ->disabled(fn($record) => $record->status == 'voided' || $record->status == 'paid')
                ->form([
                    Textarea::make('remarks')
                        ->required()
                ])
                ->color('danger')
                ->action(function($record,$data) {

                    $record->update([
                        'status' => 'voided',
                        'remarks' => $data['remarks']
                    ]);
                    
                    $record->items()->each(function($item) use ($record){
                        
                        $stock = Stock::where('supply_id', $item->supply_id)
                            ->where('branch_id', $record->branch_id)
                            ->first();

                        if ($stock) {
                            $stock->quantity += $item->quantity;
                            $stock->save();
                        }

                    });
                    Notification::make()
                        ->title('Transaction successfully voided')
                        ->success()
                        ->send();
                }),

            Tables\Actions\ViewAction::make()
                ->icon('heroicon-m-eye')
                ->label('View'),
        ])
        ->bulkActions([]);
    }       

    public static function paymentForm($record)
    {
        return [
            Section::make('Transaction Info')
                ->schema([
                    TextInput::make('id')
                        ->label('Transaction No.')
                        ->formatStateUsing(fn()=>'TX - ' . $record->id)
                        ->nullable()
                        ->disabled()
                        ->dehydrated()
                        ->maxLength(100),

                    Forms\Components\DatePicker::make('transaction_date')
                        ->label('Transaction Date')
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                ])
                ->columns(2),

            Section::make('Payment')->schema([
                Select::make('payment_method')
                    ->options([
                        'g-cash' => 'G-Cash',
                        'cash' => 'Cash',
                    ])
                    ->default('g-cash')
                    ->live(),

                Split::make([
                    TextInput::make('reference_number')
                        ->visible(fn($get) => $get('payment_method') === 'g-cash')
                        ->label('Reference No.')
                        ->required(),

                    TextInput::make('total_amount')
                        ->label('Amount')
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}/edit'),
        ];
    }
}
