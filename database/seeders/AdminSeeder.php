<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin-super@gmail.com', 
            'password' => Hash::make('password'), 
        ]);

        $employeeData = Employee::factory()->make([
        ]); 
    
        $user->employee()->create($employeeData->toArray()); 
        $user->assignRole('super-admin');
    }
}
