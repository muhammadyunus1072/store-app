<?php

namespace App\Repositories\Rsmh\Sakti\InterkoneksiSaktiSetting;

use App\Models\Rsmh\Sakti\InterkoneksiSaktiSetting;
use App\Repositories\MasterDataRepository;

class InterkoneksiSaktiSettingRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return InterkoneksiSaktiSetting::class;
    }

    public static function datatable()
    {
        return InterkoneksiSaktiSetting::query();
    }
}
