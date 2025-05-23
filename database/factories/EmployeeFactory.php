<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>w
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'contact_no' => fake()->phoneNumber,
            'employee_avatar' => 'employee-profile-image/default-profile.png',
            'email' => fake()->email,
            'address' => fake()->address,
            'status' => 'active',
        ];
    }
}
