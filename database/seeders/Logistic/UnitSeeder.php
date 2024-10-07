<?php

namespace Database\Seeders\Logistic;

use App\Models\Logistic\Master\Unit\Unit;
use App\Models\Logistic\Master\Unit\UnitDetail;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'title' => 'Berat',
                'details' => [
                    [
                        'unit_id' => 1,
                        'is_main' => 1,
                        'name' => 'Kg',
                        'value' => 1,
                    ],
                    [
                        'unit_id' => 1,
                        'is_main' => 0,
                        'name' => 'g',
                        'value' => 0.001,
                    ],
                ],
            ],
            [
                'title' => 'Panjang',
                'details' => [
                    [
                        'unit_id' => 2,
                        'is_main' => 1,
                        'name' => 'm',
                        'value' => 1,
                    ],
                    [
                        'unit_id' => 2,
                        'is_main' => 0,
                        'name' => 'cm',
                        'value' => 0.01,
                    ],
                    [
                        'unit_id' => 2,
                        'is_main' => 0,
                        'name' => 'mm',
                        'value' => 0.001,
                    ]
                ],
            ],
            [
                'title' => 'Box (12)',
                'details' => [
                    [
                        'unit_id' => 3,
                        'is_main' => 1,
                        'name' => 'Pcs',
                        'value' => 1,
                    ],
                    [
                        'unit_id' => 3,
                        'is_main' => 0,
                        'name' => 'Box',
                        'value' => 12,
                    ],
                ],
            ],
            [
                'title' => 'Jasa',
                'details' => [
                    [
                        'unit_id' => 4,
                        'is_main' => 1,
                        'name' => 'Layanan',
                        'value' => 1,
                    ],
                ],
            ],
        ];


        foreach ($data as $item) {
            Unit::create([
                'title' => $item['title']
            ]);

            foreach ($item['details'] as $detail) {
                UnitDetail::create([
                    'unit_id' => $detail['unit_id'],
                    'is_main' => $detail['is_main'],
                    'name' => $detail['name'],
                    'value' => $detail['value'],
                ]);
            }
        }
    }
}
