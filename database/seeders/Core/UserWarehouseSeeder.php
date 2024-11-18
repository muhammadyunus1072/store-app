<?php

namespace Database\Seeders\Core;

use Illuminate\Database\Seeder;
use App\Repositories\Core\User\UserWarehouseRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserWarehouseRepository::create([
            'user_id' => 1,
            'warehouse_id' => 1,
        ]);
        UserWarehouseRepository::create([
            'user_id' => 1,
            'warehouse_id' => 2,
        ]);
        UserWarehouseRepository::create([
            'user_id' => 1,
            'warehouse_id' => 3,
        ]);

        UserWarehouseRepository::create([
            'user_id' => 2,
            'warehouse_id' => 1,
        ]);
        UserWarehouseRepository::create([
            'user_id' => 2,
            'warehouse_id' => 2,
        ]);
        UserWarehouseRepository::create([
            'user_id' => 2,
            'warehouse_id' => 3,
        ]);
    }
}
