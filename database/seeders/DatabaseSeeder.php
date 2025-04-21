<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Supply;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class);

        for ($i = 0; $i < 10; $i++) {
            $employeeData = Employee::factory()->make(); 
        
            $user = User::create([
                'name' => $employeeData->first_name . ' ' . $employeeData->last_name,
                'email' => $employeeData->email, 
                'password' => Hash::make('password'), 
            ]);
        
            $user->employee()->create($employeeData->toArray()); 

            $randomRole = Arr::random(['sales-clerk', 'cashier']);
            $user->assignRole($randomRole);
        }

        Branch::factory(2)
            ->create();

        Supply::factory(100)
            ->create();

        $this->call(AdminSeeder::class);
        $this->call(SupplyCategorySeeder::class);
        $this->call(StockSeeder::class);


    }
}
