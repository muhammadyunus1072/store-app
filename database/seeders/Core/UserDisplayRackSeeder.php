<?php

namespace Database\Seeders\Core;

use Illuminate\Database\Seeder;
use App\Repositories\Core\User\UserDisplayRackRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserDisplayRackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserDisplayRackRepository::create([
            'user_id' => 1,
            'display_rack_id' => 1,
        ]);
    }
}
