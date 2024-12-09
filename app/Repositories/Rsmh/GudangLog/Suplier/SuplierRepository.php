<?php

namespace App\Repositories\Rsmh\GudangLog\Suplier;

use App\Models\Rsmh\GudangLog\Suplier;
use App\Repositories\MasterDataRepository;

class SuplierRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Suplier::class;
    }
    
    public static function getSync($limit, $offset)
    {
        return Suplier::select(
            'NM_SUPLIER as name',
            'ID_SUPLIER as kode_simrs',
        )
        ->limit($limit)
        ->offset($offset)
        ->get();
    }
}
