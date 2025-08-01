<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Enums\RolesEnum;
use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use App\Traits\CreateActionLabel;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEmployee extends CreateRecord
{
    use CreateActionLabel;
    
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): Model
    {

        $user = User::create([
            'name' => $data["first_name"] .' '.$data["last_name"],
            'email' => $data["email"],
            'password' => bcrypt('password'),
        ]);

        foreach($data['role'] as $role){
            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
        }

        $data['user_id'] = $user->id;

        $record = static::getModel()::create($data);

        if($data['branch']){
            if($user->hasRole(RolesEnum::MANAGER->value)){
                foreach($data['branch'] as $branch){
                    $record->branch()->create([
                        'branch_id'   => $branch,
                        'employee_id' => $record->id,
                        'status'      => 'active',
                    ]);
                }
            }else{
                    $record->branch()->create([
                    'branch_id'   => $data['branch'],
                    'employee_id' => $record->id,
                    'status'      => 'active',
                ]);
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
        
    }
}
