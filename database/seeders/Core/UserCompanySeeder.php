<?php

namespace Database\Seeders\Core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Core\User\UserCompany;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserCompany::create([
            'user_id' => 1,
            'company_id' => 1,
        ]);
    }
}
