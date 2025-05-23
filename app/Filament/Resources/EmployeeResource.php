<?php

namespace App\Filament\Resources;

use App\Enums\RolesEnum;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Branch;
use App\Models\Employee;
use App\Tables\Actions\CustomImpersonate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Branch Management';
    protected static ?string $navigationLabel = 'Employee';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([  
                    Forms\Components\Fieldset::make('')
                        ->schema([
                            Forms\Components\Section::make('')
                                ->schema([
                                    Forms\Components\FileUpload::make('employee_avatar')
                                        ->hiddenLabel()
                                        ->avatar()
                                        ->image()
                                        ->columnStart(1)
                                        ->directory('employee-profile-image/')
                                        ->extraAttributes([
                                            'class' => 'w-50 h-50 mx-auto flex justify-center items-center rounded-full object-cover'
                                        ]),
                                ])->grow(false),
                    ])->grow(false),
                    Forms\Components\Fieldset::make()
                        ->schema([
                    Forms\Components\Section::make('Personal Info')
                        ->schema([

                            Forms\Components\Split::make([              
                                Forms\Components\TextInput::make('first_name'),
                                Forms\Components\TextInput::make('last_name'),

                            ]),

                            Forms\Components\Split::make([              
                                Forms\Components\TextInput::make('email')
                                    ->unique(ignoreRecord:true)
                                    ->required(),
                                Forms\Components\TextInput::make('contact_no'),
                            ]),
                            Forms\Components\Textarea::make('address'),
                        ]),

                    Forms\Components\Section::make('Roles')
                        ->schema([

                                Forms\Components\Select::make('role')  
                                    ->label('Position')
                                    ->multiple()
                                    ->options(function($state){
                                        $options = RolesEnum::toArray(excludeAdmin:true);
                                        return $options;
                                    })
                                    ->required()
                                    ->live()
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('branch')  
                                    ->label('Branch')
                                    ->options(Branch::getOptionsArray(false)) 
                                    ->allowHtml() 
                                    ->multiple(fn ($get) => in_array(RolesEnum::MANAGER->value, $get('role'))?true:false)
                                    ->columnSpanFull(),

                            ])  ->columnSpanFull(),

                    ])    
                ])
                ->columnSpanFull()
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->leftJoin('users', 'employees.user_id', '=', 'users.id')
                    ->leftJoin('model_has_roles', function ($join) {
                        $join->on('users.id', '=', 'model_has_roles.model_id')
                            ->where('model_has_roles.model_type', '=', \App\Models\User::class);
                    })
                    ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->leftJoin('branch_employees','branch_employees.employee_id','employees.id')
                    ->whereNot('roles.name','super-admin')
                    ->whereNot('users.id',auth_user()->id)
                    ->when(!auth_user()->hasRole(['admin','super-admin']), function ($query) {
                        $query->whereIn('branch_employees.branch_id', auth_user()->employee->branch()->pluck('branch_id'));
                    })
                    ->select('employees.*', \DB::raw("MIN(roles.name) as role_name"))
                    ->groupBy('employees.id')
                    ->orderByRaw("CASE WHEN MIN(roles.name) = 'admin' THEN 0 ELSE 1 END")
                    ->orderBy('last_name');

            })
            ->columns([
                Panel::make([
                Split::make([
                    ImageColumn::make('employee_avatar')
                        ->circular()
                        ->defaultImageUrl(url('/images/default-profile.png'))
                        ->size(50)
                        ->grow(false),
                    TextColumn::make('full_name')
                        ->description('Name')
                        ->weight(FontWeight::Bold)
                        ->searchable(query: function (Builder $query, string $search): Builder {
                            return  $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                        })
                        ->default('-'),
                    TextColumn::make('branch.branch.name')
                        ->label('Branch')
                        ->listWithLineBreaks()
                        ->description(function($record){

                            if($record->user->hasRole(['admin'])){
                                return 'All Access';
                            }

                           return $record->branch()->first()?->branch?->name ? 'Branch':'No Branch Assigned';
                        })
                        ->color(fn ($state) => $state ? 'primary' : 'danger')
                        ->icon(fn ($state) => $state ? null : 'heroicon-m-exclamation-triangle')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->default(fn($record)=>$record->user->hasRole(['admin'])?'':'-'),

                    TextColumn::make('user.roles.name')
                        ->formatStateUsing(strFormat())
                        ->listWithLineBreaks()
                        ->bulleted(),
                    Stack::make([
                        TextColumn::make('contact_no')
                            ->icon('heroicon-m-phone'),
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                    ])
                    ->alignment(Alignment::End) 
                ])  
                ->from('md'),
            ])
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch.branch', 'name')
                    ->label('Branch')
                    ->searchable()
                    ->preload()
                    ->placeholder('All Branches'),
            
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->multiple()
                    ->options(fn()=>
                        RolesEnum::toArray(true)
                    )
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values'])) {
                            $query->whereHas('user.roles', function ($query) use ($data) {
                                $query->whereIn('name', $data['values']);
                            });
                        }
                    })
                    ->placeholder('All Roles'),
                
            ])
            ->recordUrl(function($record){
                if(auth_user()->hasRole(['manager'])){
                    return route('filament.admin.resources.employees.view',['record'=>$record->id]);
                }else{
                    return route('filament.admin.resources.employees.edit',['record'=>$record->id]);
                }
            })
            ->actions([
                CustomImpersonate::make()
            ])
            ->actionsPosition(ActionsPosition::AfterColumns)
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
            'view' => Pages\ViewEmployee::route('/{record}/view'),
        ];
    }
}
