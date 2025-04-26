<?php

namespace Database\Seeders\Logistic;

use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Product\ProductCategory;
use App\Models\Logistic\Master\Product\ProductUnit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Satuan 1=Panjang, 2=Berat, 3=Box(12)

        $data = [
            [
                'name' => 'DJI SAM SOE 12 EDISI',
                'plu' => '101879',
                'unit_id' => 1,
                'type' => Product::TYPE_PRODUCT_WITH_STOCK,
                'categories' => [
                    [
                        'product_id' => 1,
                        'category_product_id' => 1,
                    ],
                ],
                'units' => [
                    [
                        'product_id' => 1,
                        'unit_id' => 1,
                        'unit_detail_id' => 1,
                        'selling_price' => 12000,
                        'code' => '111',
                    ],
                ],
            ],
            [
                'name' => 'INDOMIE AYAM BAWANG 40 AB',
                'plu' => '103427',
                'unit_id' => 3,
                'type' => Product::TYPE_PRODUCT_WITH_STOCK,
                'categories' => [
                    [
                        'product_id' => 2,
                        'category_product_id' => 2,
                    ],
                ],
                'units' => [
                    [
                        'product_id' => 2,
                        'unit_id' => 3,
                        'unit_detail_id' => 4,
                        'selling_price' => 3200,
                        'code' => '222',
                    ],
                    [
                        'product_id' => 2,
                        'unit_id' => 3,
                        'unit_detail_id' => 5,
                        'selling_price' => 150000,
                        'code' => '333',
                    ],
                ],
            ],
            [
                'name' => 'JANNATI ROTI CREAM MOCCA ISI 4',
                'plu' => '103540',
                'unit_id' => 2,
                'type' => Product::TYPE_PRODUCT_WITH_STOCK,
                'categories' => [
                    [
                        'product_id' => 3,
                        'category_product_id' => 1,
                    ],
                ],
                'units' => [
                    [
                        'product_id' => 3,
                        'unit_id' => 2,
                        'unit_detail_id' => 2,
                        'selling_price' => 5000,
                        'code' => '444',
                    ],
                    [
                        'product_id' => 3,
                        'unit_id' => 2,
                        'unit_detail_id' => 3,
                        'selling_price' => 47000,
                        'code' => '555',
                    ],
                ],
            ],
        ];


        foreach ($data as $item) {
            Product::create([
                'plu' => $item['plu'],
                'name' => $item['name'],
                'unit_id' => $item['unit_id'],
                'type' => $item['type']
            ]);

            foreach ($item['categories'] as $detail) {
                ProductCategory::create([
                    'product_id' => $detail['product_id'],
                    'category_product_id' => $detail['category_product_id'],
                ]);
            }
            foreach ($item['units'] as $detail) {
                ProductUnit::create([
                    'product_id' => $detail['product_id'],
                    'unit_id' => $detail['unit_id'],
                    'unit_detail_id' => $detail['unit_detail_id'],
                    'selling_price' => $detail['selling_price'],
                    'code' => $detail['code'],
                ]);
            }
        }
    }
}
