<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
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
    protected static ?string $navigationLabel = 'Sale Transactions';
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
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Txn ID')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->label('Payment')->sortable(),
                Tables\Columns\TextColumn::make('payment_reference')->label('Reference')->toggleable(),
                Tables\Columns\TextColumn::make('total_amount')->money('PHP')->sortable(),
                Tables\Columns\TextColumn::make('discount_value')->label('Discount')->money('PHP'),
                Tables\Columns\TextColumn::make('date_paid')->label('Date Paid')->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'Paid',
                        'danger' => 'Voided',
                    ]),
                Tables\Columns\TextColumn::make('processedBy.name')->label('Processed By')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->options([
                        'Cash' => 'Cash',
                        'GCash' => 'GCash',
                    ])
                    ->label('Payment Method'),

                SelectFilter::make('status')
                    ->options([
                        'Paid' => 'Paid',
                        'Voided' => 'Voided',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            // RelationManagers\ItemsRelationManager::class, // Shows sale items
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
