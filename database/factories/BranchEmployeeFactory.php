<?php

namespace Database\Factories;

use App\Enums\EmployeePositionStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BranchEmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => fake()->company,
            'address' => fake()->address,
            'position' => fake()->jobTitle,
            'status' => EmployeePositionStatusEnum::Active,
        ];
    }
}
