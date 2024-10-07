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
            'name' => 'Material',
        ]);
        CategoryProduct::create([
            'name' => 'Logam',
        ]);
        CategoryProduct::create([
            'name' => 'Barang Kecil',
        ]);
        CategoryProduct::create([
            'name' => 'Jasa ',
        ]);
    }
}
