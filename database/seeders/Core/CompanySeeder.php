<?php

namespace Database\Seeders\Core;

use App\Models\Core\Company\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Minimarket Pekoren',
        ]);
    }
}
