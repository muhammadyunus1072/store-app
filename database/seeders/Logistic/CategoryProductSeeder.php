<?php

namespace Database\Seeders\Logistic;

use App\Models\Logistic\Master\CategoryProduct\CategoryProduct;
use Illuminate\Database\Seeder;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryProduct::create([
            'name' => 'MAKANAN',
        ]);
        CategoryProduct::create([
            'name' => 'MIE INSTAN',
        ]);
        CategoryProduct::create([
            'name' => 'MINUMAN',
        ]);
        CategoryProduct::create([
            'name' => 'ROKOK ',
        ]);
    }
}
