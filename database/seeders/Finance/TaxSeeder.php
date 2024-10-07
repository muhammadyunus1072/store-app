<?php

namespace Database\Seeders\Finance;

use App\Models\Finance\Master\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tax::create([
            'name' => 'PPN',
            'type' => Tax::TYPE_PPN,
            'value' => 11,
            'is_active' => 1,
        ]);
    }
}
