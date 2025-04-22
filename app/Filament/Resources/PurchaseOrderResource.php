<?php

namespace App\Filament\Resources;

use App\Enums\PurchaseOrderStatusEnums;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrdersResource\RelationManagers\OrderedItemsRelationManager;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make()
                ->schema([

                    Forms\Components\Select::make('branch_id')
                        ->label('Branch')
                        ->required()
                        ->allowHtml()
                        ->options(Branch::getOptionsArray())
                        ->columnSpanFull(),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->required()
                        ->searchable()
                        ->allowHtml()
                        ->columnSpanFull()
                        ->options(Supplier::getOptionsArray()),
        
                    Forms\Components\Select::make('prepared_by')
                        ->label('Prepared By')
                        ->required()
                        ->allowHtml()
                        ->searchable()
                        ->options(fn () => User::getOptionsArray())
                        ->default(fn () => auth()->user()?->id)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpanFull(),
        
                    Forms\Components\Textarea::make('remarks')
                        ->label('Remarks')
                        ->columnSpanFull(),
                ])

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Prepared By')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('PHP', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add filter definitions here
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make()
                        ->label('Edit Purchase Order')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning'),

                    Tables\Actions\Action::make('approve-delivery')
                        ->label('Approve Purchase Order')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Model $record) {
                            $record->acceptDelivery();
                            notification('The purchase order has been successfully approved.');
                        })
                        ->visible(fn ($record) => $record->status == PurchaseOrderStatusEnums::PENDING->value),
                    
                    Tables\Actions\Action::make('create-delivery')
                        ->label('Initiate Delivery Process')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->action(function (Model $record) {
                            $record->acceptDelivery();
                            notification('Delivery process has been initiated for this purchase order.');
                        })
                        ->visible(fn ($record) => $record->status == PurchaseOrderStatusEnums::APPROVED->value),
                    
                    Tables\Actions\Action::make('accept-delivery')
                        ->label('Confirm Delivery Received')
                        ->icon('heroicon-o-check-badge')
                        ->color('primary')
                        ->action(function (Model $record) {
                            $record->acceptDelivery();
                            notification('The delivery has been successfully confirmed.');
                        })
                        ->visible(fn ($record) => $record->status == PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value),
                    
                ])
            ], ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderedItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
