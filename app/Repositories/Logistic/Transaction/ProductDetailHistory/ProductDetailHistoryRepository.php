<?php

namespace App\Repositories\Logistic\Transaction\ProductDetailHistory;

use App\Models\Logistic\Transaction\ProductDetailHistory;
use Illuminate\Support\Facades\DB;
use App\Repositories\MasterDataRepository;

class ProductDetailHistoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductDetailHistory::class;
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
