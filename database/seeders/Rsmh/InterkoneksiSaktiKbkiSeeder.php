<?php

namespace Database\Seeders\Logistic;

use App\Models\Rsmh\InterkoneksiSakti\InterkoneksiSaktiKbki;
use Illuminate\Database\Seeder;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InterkoneksiSaktiKbki::create([
            'name' => 'Barang PDN',
        ]);
        InterkoneksiSaktiKbki::create([
            'name' => 'Barang TKDN',
        ]);
        InterkoneksiSaktiKbki::create([
            'name' => 'Barang Import',
        ]);
    }
}
