<?php

namespace Database\Seeders\Logistic;

use App\Models\Core\Setting\Setting;
use App\Settings\SettingLogistic;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'name' => SettingLogistic::NAME,
            'setting' => json_encode(SettingLogistic::ALL),
        ];

        Setting::create($data);
    }
}
