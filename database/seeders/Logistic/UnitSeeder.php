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
                'title' => 'Pcs',
                'details' => [
                    [
                        'unit_id' => 1,
                        'is_main' => 1,
                        'name' => 'Pcs',
                        'value' => 1,
                    ],
                ],
            ],
            [
                'title' => 'Pack (10)',
                'details' => [
                    [
                        'unit_id' => 2,
                        'is_main' => 1,
                        'name' => 'Pcs',
                        'value' => 1,
                    ],
                    [
                        'unit_id' => 2,
                        'is_main' => 0,
                        'name' => 'Pack',
                        'value' => 10,
                    ],
                ],
            ],
            [
                'title' => 'Box (48)',
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
                        'value' => 48,
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
