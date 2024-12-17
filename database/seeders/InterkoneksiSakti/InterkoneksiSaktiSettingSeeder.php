<?php

namespace Database\Seeders\InterkoneksiSakti;

use App\Models\Rsmh\Sakti\InterkoneksiSaktiSetting;
use App\Settings\InterkoneksiSaktiSetting as SettingsInterkoneksiSaktiSetting;
use Illuminate\Database\Seeder;

class InterkoneksiSaktiSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = SettingsInterkoneksiSaktiSetting::ALL;

        InterkoneksiSaktiSetting::create($data);
    }
}
