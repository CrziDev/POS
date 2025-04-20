<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Branch Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([  
                    Forms\Components\Section::make()
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
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Split::make([              
                                Forms\Components\TextInput::make('first_name'),
                                Forms\Components\TextInput::make('last_name'),

                            ]),
                            Forms\Components\Split::make([              
                                Forms\Components\TextInput::make('email'),
                                Forms\Components\TextInput::make('contact_no'),

                            ])
                        ])
                ])
                ->columnSpanFull()
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('employee_avatar')
                        ->circular()
                        ->defaultImageUrl(url('/images/default-profile.png'))
                        ->size(50)
                        ->grow(false),
                    TextColumn::make('full_name')
                        ->description('Name')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->default('-'),
                    TextColumn::make('branch.branch.name')
                        ->description('Branch')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->default('-'),
                    TextColumn::make('user.roles.name')
                        ->formatStateUsing(strFormat())
                        ->listWithLineBreaks()
                        ->bulleted(),
                    Stack::make([
                        TextColumn::make('contact_no')
                            ->icon('heroicon-m-phone'),
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope'),
                    ])
                    ->alignment(Alignment::End)
                    ->visibleFrom('md'),
                ])  
                ->from('md')
            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
