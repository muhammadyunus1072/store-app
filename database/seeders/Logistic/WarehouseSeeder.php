<?php

namespace Database\Seeders\Logistic;

use Illuminate\Database\Seeder;
use App\Models\Logistic\Master\Warehouse\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::create([
            'name' => 'Gudang Utama',
        ]);
        Warehouse::create([
            'name' => 'Gudang Luar',
        ]);
    }
}
