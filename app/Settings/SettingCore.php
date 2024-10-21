<?php

namespace App\Settings;

use App\Repositories\Core\Setting\SettingRepository;
use Illuminate\Support\Facades\Log;

class SettingCore
{
    const NAME = "core";

    const MULTIPLE_COMPANY = "multiple_company";

    const ALL = [
        self::MULTIPLE_COMPANY => false,
    ];

    public $parsedSetting;

    public function __construct()
    {
        $setting = SettingRepository::findBy(whereClause: [['name', self::NAME]]);
        $this->parsedSetting = json_decode($setting->setting, true);
    }

    public static function get($key)
    {
        $setting = app(self::class);

        if (!isset($setting->parsedSetting[$key])) {
            return null;
        }

        return $setting->parsedSetting[$key];
    }
}
