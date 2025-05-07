<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleTransactionReturnResource\Pages;
use App\Filament\Resources\SaleTransactionReturnResource\RelationManagers;
use App\Models\SaleTransactionReturn;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleTransactionReturnResource extends Resource
{
    protected static ?string $model = SaleTransactionReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\Section::make([

                    Forms\Components\Split::make([
                        
                        Select::make('customer')
                            ->searchable()
                            ->placeholder('Select a Customer'),

                        Select::make('transaction_id')
                            ->label('Transaction Number')
                            ->placeholder(''),
                    ]),

                    DatePicker::make('date_transaction')
                        ->label('Transaction Date')
                        ->disabled(),

                    Select::make('processed_by')
                        ->label('Processed By')
                        ->disabled()
                ]),

                Forms\Components\Section::make([

                    Repeater::make('Items')
                        ->visible(fn($get)=>$get('transaction_id'))
                        ->schema([
                            TextInput::make('name')->required(),
                            Select::make('role')
                                ->options([
                                    'member' => 'Member',
                                    'administrator' => 'Administrator',
                                    'owner' => 'Owner',
                                ])
                                ->required(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columns(2)
                ])


                // Forms\Components\TextInput::make('sale_transaction_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\TextInput::make('branch_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\DateTimePicker::make('returned_at'),
                // Forms\Components\Textarea::make('remarks')
                //     ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_transaction_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('returned_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSaleTransactionReturns::route('/'),
            'create' => Pages\CreateSaleTransactionReturn::route('/create'),
            'edit' => Pages\EditSaleTransactionReturn::route('/{record}/edit'),
        ];
    }
}
