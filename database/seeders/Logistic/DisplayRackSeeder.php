<?php

namespace Database\Seeders\Logistic;

use Illuminate\Database\Seeder;
use App\Models\Logistic\Master\DisplayRack\DisplayRack;

class DisplayRackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DisplayRack::create([
            'name' => 'Display 1',
        ]);
    }
}
