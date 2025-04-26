<?php

namespace App\Repositories\Logistic\Report\Warehouse\HistoryStock;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;

class HistoryStockRepository
{
    public static function getStartInfo(
        $productId,
        $dateStart,
        $warehouseId,
    ) {
        return ProductDetailHistoryRepository::queryLastStock(
            thresholdDate: $dateStart,
            groupBy: [],
            whereClause: [
                ['product_id', '=', $productId],
                ['warehouse_id', '=', $warehouseId]
            ]
        )->first();
    }

    public static function getHistories(
        $productId,
        $dateStart,
        $dateEnd,
        $warehouseId,
    ) {
        return ProductDetailHistory::select(
            'product_detail_histories.id',
            'product_detail_histories.transaction_date',
            'product_detail_histories.quantity',
            'product_detail_histories.remarks_id',
            'product_detail_histories.remarks_type',
            'product_details.price',
        )
            ->join('product_details', function ($join) {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id')
                    ->whereNull('product_details.deleted_at');
            })
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->orderBy('transaction_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }
}
