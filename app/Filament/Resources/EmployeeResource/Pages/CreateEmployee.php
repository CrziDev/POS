<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEmployee extends CreateRecord
{
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
            $record->branch()->create([
                'branch_id'   => $data['branch'],
                'employee_id' => $record->id,
                'status'      => 'active',
            ]);
        }

        return $record;
    }
}


// foreach($data['role'] as $role){
//     if (!$record->hasRole($role)) {
//         $record->assignRole($role);
//     }
// }
// $record->update($data);
// return $record;