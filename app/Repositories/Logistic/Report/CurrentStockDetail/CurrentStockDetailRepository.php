<?php

namespace App\Repositories\Logistic\Report\CurrentStockDetail;

use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;

class CurrentStockDetailRepository
{
    public static function datatable(
        $search,
        $dateStart,
        $dateEnd,
        $products,
        $categoryProducts
    ) {
        // QUERY : LAST STOCK
        $queryLastStock = ProductDetailHistoryRepository::queryLastStock(
            thresholdDate: $dateEnd,
            groupBy: [
                'product_id',
                'price',
                'entry_date',
                'code',
                'batch',
                'expired_date',
            ]
        );

        // QUERY : TRANSACTIONS
        $querySumTransaction = ProductDetailHistoryRepository::querySumTransactions(
            dateStart: $dateStart,
            dateEnd: $dateEnd,
            remarksTypes: [
                'stock_expense' => [['product_detail_histories.remarks_type', '=', StockExpenseProduct::class]],
                'purchase_order' => [['product_detail_histories.remarks_type', '=', PurchaseOrderProduct::class]],
            ],
            groupBy: [
                'product_id',
                'price',
                'entry_date',
                'code',
                'batch',
                'expired_date',
            ]
        );

        return Product::select(
            'products.name',
            'unit_details.name as unit_detail_name',
            'stocks.price as price',
            'stocks.entry_date as entry_date',
            'stocks.code as code',
            'stocks.batch as batch',
            'stocks.expired_date as expired_date',
            'stocks.quantity as stock_quantity',
            'stocks.value as stock_value',
            'transactions.quantity_stock_expense',
            'transactions.value_stock_expense',
            'transactions.quantity_purchase_order',
            'transactions.value_purchase_order',
        )
            ->join('units', function ($join) {
                $join->on('units.id', '=', 'products.unit_id')
                    ->whereNull('units.deleted_at');
            })
            ->join('unit_details', function ($join) {
                $join->on('units.id', '=', 'unit_details.unit_id')
                    ->where('unit_details.is_main', 1)
                    ->whereNull('unit_details.deleted_at');
            })
            ->leftJoinSub($queryLastStock, 'stocks', function ($join) {
                $join->on('products.id', '=', 'stocks.product_id');
            })
            ->leftJoinSub($querySumTransaction, 'transactions', function ($join) {
                $join->on('stocks.product_id', '=', 'transactions.product_id')
                    ->on('stocks.price', '=', 'transactions.price')
                    ->on('stocks.entry_date', '=', 'transactions.entry_date')
                    ->on('stocks.code', '=', 'transactions.code')
                    ->on('stocks.batch', '=', 'transactions.batch')
                    ->on('stocks.expired_date', '=', 'transactions.expired_date');
            })
            ->when(!empty($products), function ($query) use ($products) {
                $query->whereIn('products.id', $products);
            })
            ->when($categoryProducts, function ($query) use ($categoryProducts) {
                $query->whereHas('productCategories', function ($query) use ($categoryProducts) {
                    $query->whereIn('category_product_id', $categoryProducts);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where('products.name', env('QUERY_LIKE'), '%' . $search . '%');
            });
    }
}
