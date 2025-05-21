<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use App\Enums\EmployeeStatusEnum;
use App\Enums\RolesEnum;
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
                    ->label('Employee')
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
                    ->badge()
                    ->color(function($state){
                        if($state == RolesEnum::MANAGER->value){
                            return 'warning';
                        }else{
                            return 'info';
                        }
                    })
                    ->formatStateUsing(strFormat()),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(strFormat())
                    ->color(statusColor(EmployeeStatusEnum::class))
                    ->badge(),

            ])
            ->filters([
              
            ])
            ->headerActions([
                Tables\Actions\Action::make('assign-manager')
                    ->color('warning')
                    ->label('Assign Manager')
                    ->visible(fn()=>auth_user()->hasRole(['admin']))
                    ->form([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->required()
                            ->allowHtml()
                            ->searchable()
                            ->options(fn () => Employee::getOptionsArray(managers:true,branch:$this->getOwnerRecord()->id))
                            ->live()
                            ->columnSpanFull()
                    ])
                    ->action(function($data){
                        $this->getOwnerRecord()->branchEmployees()->create([
                            'employee_id' => $data['employee_id'],
                        ]);
                    }),
                    Tables\Actions\CreateAction::make()
                        ->label('Add Employee'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_detail')
                        ->icon('heroicon-m-eye')
                        ->label('View Detail')
                        ->action(function($record){
                             if(auth_user()->hasRole(['manager'])){
                                return redirect(route('filament.admin.resources.employees.view',['record'=>$record->employee_id]));
                            }else{
                                return redirect(route('filament.admin.resources.employees.edit',['record'=>$record->employee_id]));
                            }
                        }),
                    Tables\Actions\DeleteAction::make('Remove')
                        ->hidden(fn($record)=>auth_user()->hasRole(['manager']) && $record->employee->user->hasRole(['manager']))
                        ->label('Remove')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn()=>auth_user()->hasRole(['admin']))
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
