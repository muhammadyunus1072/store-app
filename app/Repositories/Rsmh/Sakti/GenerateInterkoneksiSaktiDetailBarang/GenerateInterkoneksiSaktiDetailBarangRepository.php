<?php

namespace App\Repositories\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailBarang;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;
use App\Repositories\MasterDataRepository;
use App\Models\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailBarang;

class GenerateInterkoneksiSaktiDetailBarangRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return GenerateInterkoneksiSaktiDetailBarang::class;
    }

    public static function getData($warehouseId, $dateStart, $dateEnd, $limit = null, $offset = null)
    {
        return ProductDetailHistory::select(
            'product_detail_histories.quantity',
            'product_details.price',
            'products.kode_sakti',
            'products.interkoneksi_sakti_persentase_tkdn',
            'products.interkoneksi_sakti_kategori_pdn',
            'interkoneksi_sakti_kbkis.nama as kode_kbki',
            'interkoneksi_sakti_coas.kode as kode_coa',
        )
        ->join('product_details', function ($join) {
            $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id')
                ->whereNull('product_details.deleted_at');
        })
        ->join('products', function ($join) {
            $join->on('products.id', '=', 'product_details.product_id')
                ->whereNull('products.deleted_at');
        })
        ->leftJoin('interkoneksi_sakti_kbkis', function ($join) {
            $join->on('interkoneksi_sakti_kbkis.id', '=', 'products.interkoneksi_sakti_kbki_id')
                ->whereNull('interkoneksi_sakti_kbkis.deleted_at');
        })
        ->leftJoin('interkoneksi_sakti_coas', function ($join) {
            $join->on('interkoneksi_sakti_coas.id', '=', 'products.interkoneksi_sakti_coa_id')
                ->whereNull('interkoneksi_sakti_coas.deleted_at');
        })
        ->where('product_detail_histories.quantity', '<', 0)
        ->where('product_details.warehouse_id', '=', $warehouseId)
        ->whereBetween('product_detail_histories.transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
        ->when($limit !== null && $offset !== null, function($query)use($limit, $offset)
        {
            $query->limit($limit)->offset($offset);
        })
        ->get();
    }

    public static function datatable()
    {
        return GenerateInterkoneksiSaktiDetailBarang::query();
    }
}
