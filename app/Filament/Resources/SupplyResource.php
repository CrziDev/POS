<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplyResource\Pages;
use App\Filament\Resources\SupplyResource\RelationManagers;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplyResource extends Resource
{
    protected static ?string $model = Supply::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Supplies';
    protected static ?string $navigationGroup = 'Supply Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->unique(),
                Forms\Components\Split::make([
                    Forms\Components\TextInput::make('name'),
                    Forms\Components\Select::make('category_id')
                        ->options(SupplyCategory::all()->pluck('name','id')),
                ])
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Supply #'),
                TextInputColumn::make('sku')
                    ->label('Sku')
                    ->searchable()
                    ->default('-')
                    ->extraAttributes(['style'=>'max-width:100px']),
                TextColumn::make('name')
                    ->label('Item')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->formatStateUsing(strFormat())
                    ->default('-'),
                SelectColumn::make('category_id')
                    ->label('Category')
                    ->options(SupplyCategory::all()->pluck('name','id'))
                    ->searchable()
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplies::route('/'),
        ];
    }
}
