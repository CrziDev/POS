<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Stock;
use App\Models\SupplyCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('branch_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('product_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('quantity')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
                // Forms\Components\TextInput::make('reorder_level')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supply.name')
                    ->formatStateUsing(strFormat())
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('supply.category.name')
                    ->formatStateUsing(strFormat())
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => $record->quantity <= $record->reorder_level ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->quantity <= $record->reorder_level ? 'heroicon-o-exclamation-triangle' : null)
                    ->tooltip(fn ($record) => $record->quantity <= $record->reorder_level ? 'Reorder needed' : null), 
                Tables\Columns\TextColumn::make('reorder_level')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supply_id')
                    ->label('Supply')
                    ->relationship('supply', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('supply_category')
                    ->label('Supply Category')
                    ->relationship('supply.category', 'name')  
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return SupplyCategory::all()->pluck('name', 'id'); 
                    }),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Below Reorder Level')
                    ->query(fn (Builder $query) => $query->whereColumn('quantity', '<', 'reorder_level')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('create-purchase-order')
                        ->label('Create Purchase Order')
                        ->icon('heroicon-o-clipboard-document-check'),
                ])
            ],ActionsPosition::BeforeColumns)
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
        ];
    }
}
