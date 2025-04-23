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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
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
                        ->relationship('supplier')
                        ->required()
                        ->searchable()
                        ->allowHtml()
                        ->columnSpanFull()
                        ->hint("Click the Plus Icon to Add new Supplier")
                        ->createOptionForm([
                            Forms\Components\Split::make([    
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\Select::make('contact_no')
                            ]),
                            Forms\Components\Textarea::make('adrress')
                        ])
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
                Tables\Columns\TextColumn::make('id')
                    ->label('PO Number')
                    ->formatStateUsing(fn($state)=>"#". $state)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('PHP', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('preparedBy.name')
                    ->label('Prepared By')
                    ->sortable()
                    ->formatStateUsing(strFormat())
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(strFormat())
                    ->color(statusColor(PurchaseOrderStatusEnums::class))
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make()
                        ->label('Edit Purchase Order')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn ($record) => !in_array($record->status,
                                        [PurchaseOrderStatusEnums::DELIVERED->value,
                                       ]
                        )),

                    Tables\Actions\ViewAction::make()
                        ->label('View Purchase Oorder')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->visible(fn ($record) => in_array($record->status,
                                        [PurchaseOrderStatusEnums::DELIVERED->value,
                                       ]
                        )),
                    Tables\Actions\Action::make('approve-delivery')
                        ->label('Approve Purchase Order')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve Purchase Order')
                        ->modalDescription('Are you sure you want to approve this purchase order? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, Approve')
                        ->action(function (Model $record) {
                            $record->approvePurchaseOrder();
                            notification('The purchase order has been successfully approved.');
                        })
                        ->visible(fn ($record) => $record->status == PurchaseOrderStatusEnums::PENDING->value),
                    
                    Tables\Actions\Action::make('create-delivery')
                        ->label('Initiate Delivery Process')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->modalHeading('Initiate Delivery')
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to create a pending delivery for this order?')
                        ->modalSubmitActionLabel('Yes, Proceed')
                        ->action(function (Model $record) {
                            $record->initiateDelivery();
                            notification('Delivery has been initiated for this purchase order.');
                        })
                        ->visible(fn ($record) => $record->status == PurchaseOrderStatusEnums::APPROVED->value),
                    
                    Tables\Actions\Action::make('accept-delivery')
                        ->label('Confirm Delivery')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->modalWidth(MaxWidth::SevenExtraLarge)
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Delivery')
                        ->modalDescription('You Will be redirected to View Page to Confirm The delivery')
                        ->modalSubmitActionLabel('Yes, Continue')
                        ->action(function (Model $record) {
                            // $record->acceptDelivery();
                            // notification('The delivery has been successfully confirmed.');
                            return redirect(route('filament.admin.resources.purchase-orders.view', [
                                'record' => $record,
                            ]));
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
            'view' => Pages\ViewPurchaseOrder::route('/{record}/view'),
        ];
    }
}
