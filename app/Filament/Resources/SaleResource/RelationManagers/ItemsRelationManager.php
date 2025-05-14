<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;


use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supply.name')
                    ->formatStateUsing(strFormat())
                    ->label('Supply Item'),
                Tables\Columns\TextColumn::make('original_price')
                    ->label('Retail Price')
                    ->money('PHP', true),
                Tables\Columns\TextColumn::make('price_amount')
                    ->label('Price Sold')
                    ->badge()
                    ->money('PHP', true),
                Tables\Columns\TextColumn::make('quantity')
                    ->badge()
                    ->label('Quantity Sold'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->default(fn($record) => $record->price_amount * $record->quantity)
                    ->money('PHP', true),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
