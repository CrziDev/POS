<?php

namespace Database\Seeders;

use App\Models\SupplyUnit; 
use Illuminate\Database\Seeder;

class SupplyUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['Piece', 'Single item or unit.'],
            ['Box', 'Contains multiple items packaged together.'],
            ['Gallon', 'Unit of liquid capacity equal to 3.785 liters.'],
            ['Pack', 'Group of items bundled together.'],
            ['Roll', 'Material wound into a cylindrical shape.'],
            ['Set', 'A complete group of related items.'],
            ['Meter', 'Measurement of length.'],
            ['Kilogram', 'Measurement of weight.'],
            ['Liter', 'Measurement of liquid volume.'],
        ];

        foreach ($units as $unit) {
            SupplyUnit::create([
                'name' => $unit[0],
                'description' => $unit[1],
            ]);
        }
    }
}
