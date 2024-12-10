<?php

namespace App\Repositories\Rsmh\GudangLog\SubBagian;

use Illuminate\Support\Facades\DB;
use App\Models\Rsmh\GudangLog\SubBagian;
use App\Repositories\MasterDataRepository;

class SubBagianRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return SubBagian::class;
    }
    
    public static function getSync($limit, $offset)
    {
        return SubBagian::select(
            DB::raw("BAGIAN.NM_BAGIAN || ' - ' || SUB_BAGIAN.NM_SUB AS name"),
            'SUB_BAGIAN.ID_SUB',
            'SUB_BAGIAN.ID_BAGIAN',
            'BAGIAN.ID_DIREKTORAT',
        )
        ->leftJoin('BAGIAN', function ($join) {
            $join->on('SUB_BAGIAN.ID_BAGIAN', '=', 'BAGIAN.ID_BAGIAN');
        })
        ->limit($limit)
        ->offset($offset)
        ->get();
    }
}
