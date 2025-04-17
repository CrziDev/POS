<?php

namespace Database\Seeders;

use App\Models\SupplyCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorries = [
           [
            'Tools & Equipment','Fundamental materials used in construction like lumber, concrete, bricks, and roofing materials.',
           ],
           [
            'Paint & Sundries','Various types of paints, primers, brushes, and other painting accessories for finishing works.',
           ],
           [
            'Garden & Outdoor','elated to gardening and outdoor living, including gardening tools, outdoor furniture, and lawnmowers.',
           ],
           [
            'Cleaning Supplies',''
           ],
           [
            'Home Decor','Items used for home decoration, including tiles, flooring materials, window treatments, and wallpapers.'
           ]
        ];

        foreach($categorries as $cat){
            SupplyCategory::create([
                'name' => $cat[0],
                'description' => $cat[1],
            ]);
        }
    }
}
