<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReplacementPaymentResource\Pages;
use App\Filament\Resources\ReplacementPaymentResource\RelationManagers;
use App\Models\ReplacementPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReplacementPaymentResource extends Resource
{
    protected static ?string $model = ReplacementPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Additional Payment';
    protected static ?string $navigationParentItem = 'Returned Transactions';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
              ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole(['admin','super-admin'])) {
                    return $query;
                }else{
                    return $query->whereHas('returnedTransaction',function(EloquentBuilder $q){
                        $q->whereIn('branch_id', auth()->user()->employee->branch()->pluck('branch_id'));
                    });
                }
            })
            ->columns([
                 Tables\Columns\TextColumn::make('returned_transaction_id')
                    ->label('Returned No.')
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'R - ' . $state),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->color('success')
                    ->money('PHP')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->formatStateUsing(fn($state)=>ucfirst($state)),
                Tables\Columns\TextColumn::make('reference_no'),
                Tables\Columns\TextColumn::make('date_paid')
                    ->label('Date Paid')
                    ->date(),
                Tables\Columns\TextColumn::make('processedBy.full_name'),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
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
            'index' => Pages\ListReplacementPayments::route('/'),
            'create' => Pages\CreateReplacementPayment::route('/create'),
            'edit' => Pages\EditReplacementPayment::route('/{record}/edit'),
        ];
    }
}
