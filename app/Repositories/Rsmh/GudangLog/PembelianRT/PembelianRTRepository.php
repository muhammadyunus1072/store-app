<?php

namespace App\Repositories\Rsmh\GudangLog\PembelianRT;

use App\Models\Rsmh\GudangLog\PembelianRT;
use App\Repositories\MasterDataRepository;

class PembelianRTRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PembelianRT::class;
    }
    
    public static function getSync($limit, $offset)
    {
        return PembelianRT::select(
            'ID_SUPLIER as kode_simrs',
            'NO_SPK as no_spk',
            'TGL_TERIMA_FAKTUR as transaction_date',
            'ID_BARANG as id_barang',
            'NM_BARANG as product_name',
            'Satuan as unit_name',
            'JML_BARANG_MSK as quantity',
            'HRGSAT_STLHPPN as price',
        )
        ->limit($limit)
        ->offset($offset)
        ->orderBy('TANGGAL_SPK', 'ASC')
        ->orderBy('NO_SPK', 'ASC')
        ->get();
    }
}
