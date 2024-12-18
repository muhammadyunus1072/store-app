<?php

namespace App\Repositories\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailCoa;

use Illuminate\Support\Facades\DB;
use App\Repositories\MasterDataRepository;
use App\Models\Rsmh\Sakti\InterkoneksiSaktiDetailBarang;
use App\Models\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailCoa;
use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;

class GenerateInterkoneksiSaktiDetailCoaRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return GenerateInterkoneksiSaktiDetailCoa::class;
    }

    public static function getData($limit = null, $offset = null)
    {
        return InterkoneksiSaktiDetailBarang::select(
            'no_dokumen',
            'kode_coa',
            DB::raw('SUM(jumlah_barang * harga_satuan) as nilai'),
        )
        ->groupBy('no_dokumen', 'kode_coa')
        ->when($limit !== null && $offset !== null, function($query)use($limit, $offset)
        {
            $query->limit($limit)->offset($offset);
        })
        ->get();
    }

    public static function datatable()
    {
        return GenerateInterkoneksiSaktiDetailCoa::query();
    }
}
