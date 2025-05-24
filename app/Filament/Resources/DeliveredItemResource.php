<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveredItemResource\Pages;
use App\Models\DeliveredItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveredItemResource extends Resource
{
    protected static ?string $model = DeliveredItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Delivered Items';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (isRole('admin','super-admin')) {
                    return $query->where('status','delivered');
                }else{
                    return $query
                        ->whereIn('po.branch_id', auth_user()->employee->branch()->pluck('branch_id'))
                        ->where('status','delivered');
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('po.supplier.name')
                    ->label('Supplier')
                    ->formatStateUsing(strFormat())
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('supply.name')
                    ->label('Supply')
                    ->formatStateUsing(strFormat())
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->sortable()
                    ->extraAttributes(['class' => 'max-w-[120px]']),

                Tables\Columns\TextColumn::make('price')
                    ->label('Unit Price')
                    ->money('PHP')
                    ->alignRight()
                    ->sortable()
                    ->extraAttributes(['class' => 'max-w-[140px]']),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('PHP')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Delivered')
                    ->date('F j, Y')                     ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('po.supplier_id')
                    ->label('Supplier')
                    ->relationship('po.supplier', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('supply_id')
                    ->label('Supply')
                    ->relationship('supply', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('delivered_from')
                    ->label('Delivered From')
                    ->form([
                        Forms\Components\DatePicker::make('delivered_from'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['delivered_from'], fn($q) => 
                                $q->whereDate('created_at', '>=', $data['delivered_from'])
                            );
                    }),

                Tables\Filters\Filter::make('delivered_until')
                    ->label('Delivered Until')
                    ->form([
                        Forms\Components\DatePicker::make('delivered_until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['delivered_until'], fn($q) => 
                                $q->whereDate('created_at', '<=', $data['delivered_until'])
                            );
                    }),
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveredItems::route('/'),
        ];
    }
}
