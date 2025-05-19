<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalePaymentResource\Pages;
use App\Filament\Resources\SalePaymentResource\RelationManagers;
use App\Models\SalePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalePaymentResource extends Resource
{
    protected static ?string $model = SalePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Sales Payment';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationParentItem = 'Sales Transaction';


    public static function table(Table $table): Table
    {
        return $table
             ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole(['admin','super-admin'])) {
                    return $query;
                }else{
                    return $query->whereIn('branch_id', auth()->user()->employee->branch()->pluck('branch_id'));
                }
            })
            ->columns([
                 Tables\Columns\TextColumn::make('sale_transaction_id')
                    ->label('Transaction No.')
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'TX - ' . $state),
                Tables\Columns\TextColumn::make('date_paid')
                    ->label('Date recorded')
                    ->date(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->formatStateUsing(fn($state)=>ucfirst($state)),
                Tables\Columns\TextColumn::make('payment_reference'),
                Tables\Columns\TextColumn::make('payment_reference'),
                Tables\Columns\TextColumn::make('processedBy.full_name'),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->color('success')
                    ->money('PHP')
                    ->badge(),

                
            ])
            ->filters([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalePayments::route('/'),
            'create' => Pages\CreateSalePayment::route('/create'),
            // 'edit' => Pages\EditSalePayment::route('/{record}/edit'),
        ];
    }
}
