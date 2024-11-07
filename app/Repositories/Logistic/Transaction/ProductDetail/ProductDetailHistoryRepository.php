<?php

namespace App\Repositories\Logistic\Transaction\ProductDetail;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;
use Illuminate\Support\Facades\DB;
use App\Repositories\MasterDataRepository;

class ProductDetailHistoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductDetailHistory::class;
    }

    public static function datatable($remarksIds = [], $remarksType = null)
    {
        $query = ProductDetailHistory::select(
            'product_detail_histories.id',
            'product_detail_histories.product_detail_id',
            'product_detail_histories.transaction_date',
            'product_detail_histories.start_stock',
            'product_detail_histories.quantity',
            'product_detail_histories.last_stock',
            'product_details.price',
            'product_details.entry_date',
            'product_details.expired_date',
            'product_details.batch',
            'product_details.code',
            'companies.name as company_name',
            'warehouses.name as warehouse_name',
            'products.name as product_name',
            'unit_details.name as unit_detail_name',
        )
            ->join('product_details', function ($join) {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id');
            })
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'product_details.company_id');
            })
            ->join('warehouses', function ($join) {
                $join->on('warehouses.id', '=', 'product_details.warehouse_id');
            })
            ->join('products', function ($join) {
                $join->on('products.id', '=', 'product_details.product_id');
            })
            ->join('units', function ($join) {
                $join->on('products.unit_id', '=', 'units.id');
            })
            ->join('unit_details', function ($join) {
                $join->on('units.id', '=', 'unit_details.unit_id')->where('is_main', 1);
            })
            ->when(count($remarksIds) > 0, function ($query) use ($remarksIds) {
                $query->whereIn("product_detail_histories.remarks_id", $remarksIds);
            })
            ->when($remarksType, function ($query) use ($remarksType) {
                $query->where('product_detail_histories.remarks_type', $remarksType);
            });

        return $query;
    }

    public static function findLastHistory(
        $productDetailId,
        $thresholdDate,
    ) {
        return ProductDetailHistory::where('product_detail_id', $productDetailId)
            ->where('transaction_date', '<=', $thresholdDate)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public static function getLastHistories(
        $productDetailId,
        $productId,
        $companyId,
        $warehouseId,
        $thresholdDate,
    ) {
        $queryStockRowNumber = ProductDetailHistory::select(
            'product_detail_histories.product_detail_id',
            'product_detail_histories.last_stock',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY product_detail_histories.product_detail_id ORDER BY product_detail_histories.id DESC) as rn')
        )
            ->when($productDetailId, function($query) use($productDetailId) 
            {
                $query->where('product_detail_histories.product_detail_id', $productDetailId);
            })
            ->where('product_details.product_id', $productId)
            ->where('product_details.company_id', $companyId)
            ->where('product_details.warehouse_id', $warehouseId)
            ->where('product_detail_histories.transaction_date', '<=', $thresholdDate)
            ->join('product_details', function($join)
            {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id')
                ->whereNull('product_details.deleted_at');
            });

        return DB::table($queryStockRowNumber, "histories")
            ->select(
                'product_detail_id',
                'last_stock',
            )
            ->where('last_stock', '>', 0)
            ->where('rn', '=', 1)
            ->get();
    }
}
