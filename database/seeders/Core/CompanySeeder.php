<?php

namespace Database\Seeders\Core;

use App\Models\Core\Company\Company;
use App\Models\Core\Company\CompanyWarehouse;
use Illuminate\Database\Seeder;
use App\Models\Core\User\UserCompany;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'RS Mohammad Hoesin',
        ]);
    }
}
