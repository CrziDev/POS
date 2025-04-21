<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\Branch;
use App\Models\Stock;
use App\Models\Supply;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::all()->each(function($branch){
            
            Supply::all()->each(function($product) use ($branch){
                Stock::firstOrCreate(
                        [
                            'branch_id'   => $branch->id,
                            'supply_id'  => $product->id,
                        ],
                        [
                            'quantity'      => 0,
                            'reorder_level' => 10,
                        ],
                    );
            });
        });
    }
}