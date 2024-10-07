<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Core\Setting\Setting;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Core\Setting\SettingRepository;

class SettingController extends Controller
{
    public function setting_logistic()
    {
        $data = SettingRepository::findByName(Setting::NAME_LOGISTIC);
        $objId = $data ? Crypt::encrypt($data->id) : null;
        return view('app.core.setting.logistic.index', ["objId" => $objId]);
    }
}
