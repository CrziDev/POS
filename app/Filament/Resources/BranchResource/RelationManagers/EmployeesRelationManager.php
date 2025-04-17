<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use App\Enums\EmployeeStatusEnum;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'branchEmployees';
    protected static ?string $title = 'Branch Employees';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->required()
                    ->allowHtml()
                    ->searchable()
                    ->options(fn () => Employee::getOptionsArray(true))
                    ->live()
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('employee.employee_avatar')
                    ->label('')
                    ->defaultImageUrl(url('/images/default-profile.png'))
                    ->circular(),
                Tables\Columns\TextColumn::make('employee.full_name'),
                Tables\Columns\TextColumn::make('employee.user.roles.name')
                    ->formatStateUsing(strFormat()),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(strFormat())
                    ->color(statusColor(EmployeeStatusEnum::class))
                    ->badge(),

            ])
            ->filters([
              
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Employee'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_detail')
                        ->icon('heroicon-m-eye')
                        ->label('View Detail'),
                    Tables\Actions\DeleteAction::make('Remove')
                        ->label('Remove')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
