<?php

namespace Database\Seeders\Core;

use App\Models\Core\Setting\Setting;
use App\Settings\SettingCore;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'name' => SettingCore::NAME,
            'setting' => json_encode(SettingCore::ALL),
        ];

        Setting::create($data);
    }
}
