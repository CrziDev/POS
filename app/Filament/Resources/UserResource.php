<?php
namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Enums\RolesEnum;  
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;  
use Filament\Notifications\Notification;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'User Accounts';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->label('Full Name')
                        ->required(),
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required(),
                    Select::make('role')  
                        ->label('Role')
                        ->multiple()
                        ->options(RolesEnum::toArray())  
                        ->required(),
                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->formatStateUsing(strFormat())
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->sortable(query: function ($query, $direction) {
                        $query->orderBy(
                            DB::table('model_has_roles')
                                ->select('roles.name')
                                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                ->whereColumn('model_has_roles.model_id', 'users.id')
                                ->orderBy('roles.name')
                                ->limit(1),
                            $direction
                        );
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('roles', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->formatStateUsing(strFormat())
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->options(Role::pluck('name', 'id')->toArray())
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->label('Role'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon(false)
                    ->mutateRecordDataUsing(function (array $data,$record): array {
                        $data['role'] = $record->getRoleNames();

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        foreach($data['role'] as $role){
                            if (!$record->hasRole($role)) {
                                $record->assignRole($role);
                            }
                        }
                        $record->update($data);
                        return $record;
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('change-password')
                        ->label('Change Password')
                        ->icon('heroicon-o-key')  
                        ->modalHeading('Change Password for User')  // Modal heading
                        ->form([
                            TextInput::make('password')
                                ->label('New Password')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->helperText('Minimum 8 characters')
                                ->confirmed(),
                            TextInput::make('password_confirmation')
                                ->label('Confirm New Password')
                                ->password()  
                                ->required()
                                ->same('password')
                                ->helperText('Confirm the new password'),
                        ])
                        ->action(function (array $data, $record) {
                            $record->update([
                                'password' => bcrypt($data['password']),
                            ]);

                            Notification::make()
                                ->title('Password Changed')
                                ->success()
                                ->send();
                        }),
                ]),
                // Impersonate::make(),
            ],ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

