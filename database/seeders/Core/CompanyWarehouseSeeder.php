<?php

namespace Database\Seeders\Core;

use App\Models\Core\Company\CompanyWarehouse;
use Illuminate\Database\Seeder;
use App\Models\Core\User\UserCompany;

class CompanyWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyWarehouse::create([
            'company_id' => 1,
            'warehouse_id' => 1,
        ]);
        CompanyWarehouse::create([
            'company_id' => 2,
            'warehouse_id' => 2,
        ]);
    }
}
