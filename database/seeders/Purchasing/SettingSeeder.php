<?php

namespace Database\Seeders\Purchasing;

use App\Models\Core\Setting\Setting;
use App\Settings\SettingPurchasing;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'name' => SettingPurchasing::NAME,
            'setting' => json_encode(SettingPurchasing::ALL),
        ];

        Setting::create($data);
    }
}
