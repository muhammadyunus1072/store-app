<?php

namespace App\Repositories\Core\Setting;

use App\Models\Core\Setting\Setting;
use App\Repositories\MasterDataRepository;


class SettingRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Setting::class;
    }

    public static function datatable()
    {
        return Setting::query();
    }
}
