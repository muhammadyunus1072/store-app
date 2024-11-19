<?php

namespace App\Repositories\Logistic\Report\HistoryStockDetail;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;

class HistoryStockDetailRepository
{
    public static function getStartInfo(
        $productId,
        $dateStart,
    ) {
        return ProductDetailHistoryRepository::queryLastStock(
            thresholdDate: $dateStart,
            groupBy: [
                'product_id',
                'price',
                'entry_date',
                'code',
                'batch',
                'expired_date',
            ],
            whereClause: [['product_id', '=', $productId]]
        )->get();
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
            'product_details.entry_date',
            'product_details.code',
            'product_details.batch',
            'product_details.expired_date',
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
