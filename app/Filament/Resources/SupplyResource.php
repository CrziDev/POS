<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplyResource\Pages;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Models\SupplyUnit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SupplyResource extends Resource
{
    protected static ?string $model = Supply::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Supplies';
    protected static ?string $navigationGroup = 'Supply Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make([

                Forms\Components\TextInput::make('sku')
                    ->label('Sku')
                    ->hint('Must Be Unique')
                    ->unique(ignoreRecord:true)
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Supply Name')
                    ->unique(ignoreRecord:true)
                    ->required(),
        
                Forms\Components\Grid::make(2) 
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('Item Code')
                            ->required(),
        
                        Forms\Components\TextInput::make('price')
                            ->label('Retail Price')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->minValue(1)
                            ->inputMode('decimal')
                            ->live(debounce:500),
                    ]),
        
                Forms\Components\Grid::make(2) 
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(SupplyCategory::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
        
                        Forms\Components\Select::make('unit_id')
                            ->label('Unit')
                            ->options(SupplyUnit::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ]),
            ])->columnSpanFull(),
        ]);
        
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 TextInputColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->default('-')
                    ->extraAttributes(['style' => 'max-width:120px']),
                TextColumn::make('name')
                    ->label('Item')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->default('-'),
                TextColumn::make('price')
                    ->label('Retail Price')
                    ->money('PHP')
                    ->sortable(),
                TextInputColumn::make('item_code')
                    ->label('Item Code')
                    ->searchable()
                    ->default('-')
                    ->extraAttributes(['style' => 'max-width:120px']),
                SelectColumn::make('unit_id')
                    ->label('Unit')
                    ->options(SupplyUnit::all()->pluck('name', 'id'))
                    ->searchable(),

                SelectColumn::make('category_id')
                    ->label('Category')
                    ->options(SupplyCategory::all()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'name'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->label('Edit Supply'),
                ]), 
            ], ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-m-trash'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplies::route('/'),
        ];
    }
}
