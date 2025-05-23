<?php

namespace Database\Seeders\Logistic;

use App\Models\Purchasing\Master\CategorySupplier\CategorySupplier;
use Illuminate\Database\Seeder;

class CategorySupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategorySupplier::create([
            'name' => 'MAKANAN',
        ]);
        CategorySupplier::create([
            'name' => 'MINUMAN',
        ]);
        CategorySupplier::create([
            'name' => 'ROKOK',
        ]);
    }
}
