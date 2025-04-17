<?php

namespace App\Repositories\Logistic\Master\Unit;

use App\Helpers\General\NumberFormatter;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\Unit\UnitDetail;

class UnitDetailRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return UnitDetail::class;
    }

    public static function findMainUnit($unitId)
    {
        return UnitDetail::where('unit_id', '=', $unitId)->where('is_main', 1)->first();
    }

    public static function getOptions($unitId)
    {
        $data = UnitDetail::select(
            'id',
            'is_main',
            'name',
            'value'
        )
            ->where('unit_id', $unitId)
            ->orderBy('is_main', 'desc')
            ->get()
            ->toArray();

        $mainName = "";
        foreach ($data as $index => $item) {
            if ($item['is_main']) {
                $mainName = $item['name'];
                break;
            }
        }

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
            $data[$index]['value'] = $item['value'];
            $data[$index]['value_info'] = $item['is_main'] ? "" : $item['value'] . " $mainName";
        }

        return $data;
    }
}
