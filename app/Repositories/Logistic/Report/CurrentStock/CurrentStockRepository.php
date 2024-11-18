<?php

namespace App\Repositories\Logistic\Report\CurrentStock;

use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;

class CurrentStockRepository
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
            groupBy: ['product_id']
        );

        // QUERY : TRANSACTIONS
        $querySumTransaction = ProductDetailHistoryRepository::querySumTransactions(
            dateStart: $dateStart,
            dateEnd: $dateEnd,
            remarksTypes: [
                'stock_expense' => [['product_detail_histories.remarks_type', '=', StockExpenseProduct::class]],
                'purchase_order' => [['product_detail_histories.remarks_type', '=', PurchaseOrderProduct::class]],
            ],
            groupBy: ['product_id']
        );

        return Product::select(
            'products.name',
            'unit_details.name as unit_detail_name',
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
                $join->on('stocks.product_id', '=', 'transactions.product_id');
            })
            ->where('products.type', '=', Product::TYPE_PRODUCT_WITH_STOCK)
            ->when($products, function ($query) use ($products) {
                $query->whereIn('products.id', $products);
            })
            ->when($categoryProducts, function ($query) use ($categoryProducts) {
                $query->whereHas('productCategories', function ($query) use ($categoryProducts) {
                    $query->whereIn('category_product_id', $categoryProducts);
                });
            })
            ->where(function ($query) {
                $query->where('stocks.quantity', '!=', 0)
                    ->orWhere('transactions.quantity_stock_expense', '!=', 0)
                    ->orWhere('transactions.quantity_purchase_order', '!=', 0);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('products.name', env('QUERY_LIKE'), '%' . $search . '%');
            });
    }
}
