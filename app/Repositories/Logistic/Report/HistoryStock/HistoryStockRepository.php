<?php

namespace App\Repositories\Logistic\Report\HistoryStock;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;

class HistoryStockRepository
{
    public static function getStartInfo(
        $productId,
        $dateStart,
    ) {
        return ProductDetailHistoryRepository::queryLastStock(
            thresholdDate: $dateStart,
            groupBy: [],
            whereClause: [['product_id', '=', $productId]]
        )->first();
    }

    public static function getHistories(
        $productId,
        $dateStart,
        $dateEnd,
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
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->whereNotIn('product_detail_histories.remarks_type', [StockRequestProduct::class])
            ->orderBy('transaction_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }
}
