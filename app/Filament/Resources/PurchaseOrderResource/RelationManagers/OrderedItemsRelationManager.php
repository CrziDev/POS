<?php

namespace App\Filament\Resources\PurchaseOrdersResource\RelationManagers;

use App\Enums\PurchaseOrderStatusEnums;
use App\Models\Supply;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class OrderedItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderedItems';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('supply_id')
                ->label('Supply Item')
                ->required()
                ->searchable()
                ->allowHtml()
                ->columnSpanFull()
                ->options(Supply::getOptionsArray()),

            // Forms\Components\Toggle::make('is_price_set')
            //     ->live()
            //     ->label('Specify price at delivery')
            //     ->helperText('Toggle if the price will be specified upon delivery.')
            //     ->columnSpanFull(),

            Forms\Components\Split::make([
                Forms\Components\TextInput::make('price')
                    ->label('Unit Price')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->required()
                    ->default(1)
                    ->numeric()
                    ->minValue(1)
                    ->inputMode('decimal')
                    ->live(debounce:500)
                    ->afterStateUpdated(function ($get, $set) {
                        $total = moneyToNumber($get('quantity')) * moneyToNumber($get('price'));
                        $set('total_amount',number_format($total));

                    }),
                    // ->visible(fn ($get) => !$get('is_price_set')),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live()
                    ->live(debounce:500)
                    ->afterStateUpdated(function ($get, $set) {
                        $total = moneyToNumber($get('quantity')) * moneyToNumber($get('price'));

                        $set('total_amount',number_format($total));
                    }),
            ])
            ->columnSpanFull(),

            Forms\Components\TextInput::make('total_amount')
                ->label('Total Amount')
                ->disabled()
                ->dehydrated()
                // ->required(fn ($get) => !$get('is_price_set'))
                ->stripCharacters(',')
                ->minValue(1)
                ->columnSpanFull(),
                // ->visible(fn ($get) => !$get('is_price_set')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supply.name')
                    ->label('Supply')
                    ->sortable()
                    ->formatStateUsing(strFormat())
                    ->searchable(),

                // Tables\Columns\IconColumn::make('is_price_set')
                //     ->label('Set at Delivery')
                //     ->boolean()
                //     ->falseIcon('heroicon-o-minus-circle') 
                //     ->trueIcon('heroicon-o-check-circle')
                //     ->falseColor('gray')
                //     ->trueColor('success'),


                Tables\Columns\TextColumn::make('price')
                    ->label('Unit Price')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if($record->is_price_set && ($record->price == 0)){
                            return '-';
                        }
                        return '₱' . number_format($record->total_amount, 2);
                    })
                    ->badge()
                    ->color('gray')
                    ->extraAttributes(['class'=>'max-w-[200px]']),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->badge()
                    ->color('gray')
                    ->extraAttributes(['class'=>'max-w-[200px]']),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(function ($record) {
                        if($record->is_price_set && ($record->price == 0)){
                            return '-';
                        }
                        return '₱' . number_format($record->total_amount, 2);
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Ordered Item')
                    ->icon('heroicon-o-plus')
                    ->color('info')
                    ->modalSubmitActionLabel('Save')
                    ->extraModalFooterActions(fn (CreateAction $action): array => [
                        $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                            ->label('Add new'),
                    ])
                    ->hidden(function (): bool {
                        return (
                            in_array($this->getOwnerRecord()->status,
                                [PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value,PurchaseOrderStatusEnums::APPROVED->value])
                                ?true:false
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Edit Ordered Item')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->hidden(function (): bool {
                            return (
                                in_array($this->getOwnerRecord()->status,
                                    [PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value,PurchaseOrderStatusEnums::APPROVED->value])
                                    ?true:false
                            );
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('Remove Ordered Item')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->hidden(function (): bool {
                            return (
                                in_array($this->getOwnerRecord()->status,
                                    [PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value,PurchaseOrderStatusEnums::APPROVED->value])
                                    ?true:false
                            );
                        })
                        ->requiresConfirmation(),
                ]),
            ], ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ]),
            ]);
    }

}
