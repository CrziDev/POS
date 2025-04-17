<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        for ($i = 0; $i < 10; $i++) {
            $employeeData = Employee::factory()->make(); 
        
            $user = User::create([
                'name' => $employeeData->first_name . ' ' . $employeeData->last_name,
                'email' => $employeeData->email, 
                'password' => Hash::make('password'), 
            ]);
        
            $user->employee()->create($employeeData->toArray()); 
        }

        Branch::factory(2)
            ->create();

    }
}
