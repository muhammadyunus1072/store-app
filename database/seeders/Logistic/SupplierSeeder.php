<?php

namespace Database\Seeders\Logistic;

use Illuminate\Database\Seeder;
use App\Models\Purchasing\Master\Supplier\Supplier;
use App\Models\Purchasing\Master\Supplier\SupplierCategory;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplier = Supplier::create([
            'name' => 'Kertas Jaya',
        ]);
        SupplierCategory::create([
            'supplier_id' => $supplier->id,
            'category_supplier_id' => 1,
        ]);

        $supplier = Supplier::create([
            'name' => 'Meja Kursi Sukses',
        ]);
        SupplierCategory::create([
            'supplier_id' => $supplier->id,
            'category_supplier_id' => 2,
        ]);
    }
}
