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

    public static function datatable($isShowStock = false, $remarksIds = [], $remarksType = null)
    {
        $query = ProductDetailHistory::select(
            'product_detail_histories.id',
            'product_detail_histories.product_detail_id',
            'product_detail_histories.transaction_date',
            'product_detail_histories.quantity',
            'product_detail_histories.remarks_note',
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

        if ($isShowStock) {
            $query->addSelect('product_stock_details.quantity as stock')
                ->join('product_stock_details', function ($join) {
                    $join->on('product_details.id', '=', 'product_stock_details.product_detail_id');
                });
        }

        return $query;
    }

    public static function getSumStock(
        $productDetailId,
        $productId,
        $companyId,
        $warehouseId,
        $groupByProductDetailId,
        $groupByProductId,
        $groupByCompanyId,
        $groupByWarehouseId,
    ) {
        return ProductDetailHistory::select(
            DB::raw("SUM(product_detail_histories.quantity) as sum_quantity")
        )
            ->join('product_details', function ($join) {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id');
            })
            ->when($productDetailId, function ($query) use ($productDetailId) {
                $query->where('product_detail_histories.product_detail_id', $productDetailId);
            })
            ->when($productId, function ($query) use ($productId) {
                $query->where('product_details.product_id', $productId);
            })
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('product_details.warehouse_id', $warehouseId);
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('product_details.company_id', $companyId);
            })
            ->when($groupByProductDetailId, function ($query) {
                $query->addSelect('product_detail_histories.product_detail_id')
                    ->groupBy('product_detail_histories.product_detail_id');
            })
            ->when($groupByProductId, function ($query) {
                $query->addSelect('product_details.product_id')
                    ->groupBy('product_details.product_id');
            })
            ->when($groupByWarehouseId, function ($query) {
                $query->addSelect('product_details.warehouse_id')
                    ->groupBy('product_details.warehouse_id');
            })
            ->when($groupByCompanyId, function ($query) {
                $query->addSelect('product_details.company_id')
                    ->groupBy('product_details.company_id');
            })
            ->get();
    }

    public static function getNewerHistories(ProductDetailHistory $productiDetailHistory)
    {
        return ProductDetailHistory::where('product_detail_id', $productiDetailHistory->id)
            ->where(function ($query) use ($productiDetailHistory) {
                $query->where('transaction_date', '>', $productiDetailHistory->transaction_date)
                    ->orWhere(function ($query)  use ($productiDetailHistory) {
                        $query->where('transaction_date', '=', $productiDetailHistory->transaction_date)
                            ->where('id', '>', $productiDetailHistory->id);
                    });
            })
            ->get();
    }
}
