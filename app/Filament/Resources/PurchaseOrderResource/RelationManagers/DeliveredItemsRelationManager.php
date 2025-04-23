<?php

namespace App\Filament\Resources\PurchaseOrdersResource\RelationManagers;

use App\Enums\PurchaseOrderStatusEnums;
use App\Models\Supply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DeliveredItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveredItems';


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supply.name')
                    ->label('Supply')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextInputColumn::make('quantity')
                    ->label('Qty')
                    ->extraAttributes(['class'=>'max-w-[200px]']),

                Tables\Columns\TextInputColumn::make('price')
                    ->label('Unit Price')
                    ->extraAttributes(['class'=>'max-w-[200px]']),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if($record->is_price_set && ($record->price == 0)){
                            return '-';
                        }
                        return '₱' . number_format($record->total_amount, 2);
                    }),
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->label('Remove Ordered Item')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
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

    
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return in_array($ownerRecord->status,[PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value,PurchaseOrderStatusEnums::DELIVERED->value]);
    }

}
