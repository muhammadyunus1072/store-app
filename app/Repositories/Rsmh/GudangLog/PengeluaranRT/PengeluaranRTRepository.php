<?php

namespace App\Repositories\Rsmh\GudangLog\PengeluaranRT;

use App\Models\Rsmh\GudangLog\PengeluaranRT;
use App\Repositories\MasterDataRepository;

class PengeluaranRTRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PengeluaranRT::class;
    }
    
    public static function getSync($limit, $offset)
    {
        return PengeluaranRT::select(
            'ID_SUB',
            'TANGGAL as transaction_date',
            'NO_BON as note',
            'ID_BARANG',
            'JML as quantity',
        )
        ->limit($limit)
        ->offset($offset)
        ->orderBy('TANGGAL_KEL', 'ASC')
        ->orderBy('NO_BON', 'ASC')
        ->get();
    }
}
