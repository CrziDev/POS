<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $navigationLabel = 'Branches';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Branch Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Split::make([              
                            Forms\Components\TextInput::make('name'),
                            Forms\Components\TextInput::make('contact_no'),
                            Forms\Components\DatePicker::make('date_establish')
                                ->displayFormat('F j, Y')
                                ->native(false)
                        ])
                        ->from('md'),
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (auth_user()->hasRole(['owner','super-admin'])) {
                    return $query;
                }else{
                    return $query->whereIn('id', auth_user()->employee->branch()->pluck('branch_id'));
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Branch'),
                Tables\Columns\TextColumn::make('address')
                    ->label('Location'),
                Tables\Columns\ImageColumn::make('branchEmployees.employee.employee_avatar')
                    ->label('Employees')
                    ->circular()
                    ->stacked()
                    ->limit(5)
                    ->limitedRemainingText()
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Remove')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
