<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Branch;
use App\Models\SaleTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateRangeFilter;

class SaleResource extends Resource
{
    protected static ?string $model = SaleTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Sales Transaction';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('payment_method')
                ->options([
                    'Cash' => 'Cash',
                    'GCash' => 'GCash',
                ])
                ->required(),

            Forms\Components\TextInput::make('payment_reference')
                ->label('Reference No.')
                ->nullable()
                ->maxLength(100),

            Forms\Components\DatePicker::make('date_paid')
                ->required(),

            Forms\Components\TextInput::make('discount_value')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('total_amount')
                ->numeric()
                ->disabled()
                ->dehydrated(),

            Forms\Components\Select::make('status')
                ->options([
                    'Paid' => 'Paid',
                    'Voided' => 'Voided',
                ])
                ->required(),
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
                    ->badge()
                    ->colors([
                        'success' => 'Paid',
                        'danger' => 'Voided',
                    ]),
                Tables\Columns\TextColumn::make('id')
                    ->label('Transaction #')
                    ->sortable()
                    ->formatStateUsing(fn($state)=>'TX - '. $state),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return  $query->whereHas('customer',function (Builder $sq) use($search){
                            $sq->whereRaw("name LIKE ?", ["%{$search}%"]);
                        });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->label('Payment')->sortable(),
                Tables\Columns\TextColumn::make('payment_reference')->label('Reference')->toggleable(),
                Tables\Columns\TextColumn::make('total_amount')->money('PHP')->sortable(),
                Tables\Columns\TextColumn::make('discount_value')->label('Discount')->money('PHP'),
                Tables\Columns\TextColumn::make('date_paid')
                    ->label('Date Transaction')
                    ->date(),
                Tables\Columns\TextColumn::make('employee.fullName')
                    ->label('Proccessed By')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return  $query->whereHas('employee',function (Builder $sq) use($search){
                            $sq->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        });
                    })
                    ->sortable(),         
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
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
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            // 'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
