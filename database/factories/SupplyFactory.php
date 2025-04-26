<?php

namespace Database\Factories;

use App\Models\SupplyCategory;
use App\Models\SupplyUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supply>
 */
class SupplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'category_id' => SupplyCategory::inRandomOrder()->first()?->id,
            'unit_id' => SupplyUnit::inRandomOrder()->first()?->id,
            'sku' => fake()->unique()->bothify('ITEM-####'),
        ];
    }
}
