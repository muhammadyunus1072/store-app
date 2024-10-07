<?php

namespace Database\Seeders\Logistic;

use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Product\ProductCategory;
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
                'name' => 'Semen',
                'unit_id' => 1,
                'type' => Product::TYPE_PRODUCT_WITH_STOCK,
                'categories' => [
                    [
                        'product_id' => 1,
                        'category_product_id' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Besi',
                'unit_id' => 2,
                'type' => Product::TYPE_PRODUCT_WITH_STOCK,
                'categories' => [
                    [
                        'product_id' => 2,
                        'category_product_id' => 2,
                    ],
                ],
            ],

            [
                'name' => 'Baut Mur 12mm',
                'unit_id' => 3,
                'type' => Product::TYPE_PRODUCT_WITHOUT_STOCK,
                'categories' => [
                    [
                        'product_id' => 3,
                        'category_product_id' => 3,
                    ],
                ],
            ],

            [
                'name' => 'Jasa Rakit',
                'unit_id' => 4,
                'type' => Product::TYPE_SERVICE,
                'categories' => [
                    [
                        'product_id' => 4,
                        'category_product_id' => 4,
                    ],
                ],
            ],
        ];


        foreach ($data as $item) {
            Product::create([
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
        }
    }
}
