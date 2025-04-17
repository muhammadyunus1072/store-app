<?php

namespace Database\Seeders\Core;

use App\Models\Core\Company\CompanyDisplayRack;
use Illuminate\Database\Seeder;
use App\Models\Core\User\UserCompany;

class CompanyDisplayRackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyDisplayRack::create([
            'company_id' => 1,
            'display_rack_id' => 1,
        ]);
        CompanyDisplayRack::create([
            'company_id' => 2,
            'display_rack_id' => 2,
        ]);
    }
}
