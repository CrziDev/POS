<?php

namespace App\Filament\Resources;

use App\Enums\PurchaseOrderStatusEnums;
use App\Enums\RolesEnum;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrdersResource\RelationManagers\DeliveredItemsRelationManager;
use App\Filament\Resources\PurchaseOrdersResource\RelationManagers\OrderedItemsRelationManager;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return PurchaseOrder::where('status', 'pending')
            ->whereIn('branch_id', auth_user()->employee->branch()->pluck('branch_id'))->count();
    }

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
                        ->hint("Click the Plus Icon to add a new Supplier")
                        ->createOptionForm([
                            Forms\Components\Split::make([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('contact_no'),
                            ]),
                            Forms\Components\Textarea::make('address'),
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
            ->modifyQueryUsing(function (Builder $query) {
                if (auth_user()->hasRole(['admin','super-admin'])) {
                    return $query;
                }else{
                    return $query->whereIn('branch_id', auth_user()->employee->branch()->pluck('branch_id'));
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('PO Number')
                    ->formatStateUsing(fn ($state) => "#" . $state)
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
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::getOptionsArray(false)),

                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::getOptionsArray(false)),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PurchaseOrderStatusEnums::options()),
            ])
            ->recordUrl(function($record){
                if(isRole('manager')){
                    return route('filament.admin.resources.purchase-orders.edit',['record'=>$record->id]);
                }else{
                    return route('filament.admin.resources.purchase-orders.view',['record'=>$record->id]);
                }
            })
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Edit Purchase Order')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn ($record) =>
                            !in_array($record->status, [PurchaseOrderStatusEnums::DELIVERED->value,PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value])
                            &&
                            isRole('manager')
                        ),

                    Tables\Actions\ViewAction::make()
                        ->label('View Purchase Order')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->visible(fn ($record) =>
                            in_array($record->status, [PurchaseOrderStatusEnums::DELIVERED->value])
                        ),

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
                        ->visible(fn ($record) =>
                            $record->status === PurchaseOrderStatusEnums::PENDING->value
                            &&
                            auth()->user()->hasRole([RolesEnum::ADMIN->value])
                        ),

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
                        ->visible(fn ($record) =>
                            $record->status === PurchaseOrderStatusEnums::APPROVED->value
                            &&
                            auth()->user()->hasRole([RolesEnum::ADMIN->value])
                        ),

                    Tables\Actions\Action::make('accept-delivery')
                        ->label('Confirm Delivery')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->modalWidth(MaxWidth::SevenExtraLarge)
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Delivery')
                        ->modalDescription('You will be redirected to the View Page to confirm the delivery.')
                        ->modalSubmitActionLabel('Yes, Continue')
                        ->action(function (Model $record) {
                            return redirect(route('filament.admin.resources.purchase-orders.edit', ['record' => $record]));
                        })
                        ->visible(fn ($record) =>
                            $record->status === PurchaseOrderStatusEnums::DELIVERYINPROGRESS->value
                            &&
                            auth()->user()->hasRole([RolesEnum::ADMIN->value])
                        ),
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
            DeliveredItemsRelationManager::class,

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
