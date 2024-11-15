<?php

namespace App\Repositories\Logistic\Report\CurrentStockWarehouse;

use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;

class CurrentStockWarehouseRepository
{
    public static function datatable(
        $search,
        $dateStart,
        $dateEnd,
        $products,
        $categoryProducts,
        $warehouseId
    ) {
        // QUERY : LAST STOCK
        $queryLastStock = ProductDetailHistoryRepository::queryLastStock(
            thresholdDate: $dateEnd,
            groupBy: ['product_id'],
            whereClause: [['warehouse_id', '=', $warehouseId]]
        );

        // QUERY : TRANSACTIONS
        $querySumTransaction = ProductDetailHistoryRepository::querySumTransactions(
            dateStart: $dateStart,
            dateEnd: $dateEnd,
            remarksTypes: [
                'stock_expense' => [['remarks_type', '=', StockExpenseProduct::class]],
                'purchase_order' => [['remarks_type', '=', PurchaseOrderProduct::class]],
                'stock_request_out' => [['remarks_type', '=', StockRequestProduct::class], ['quantity', '<', 0]],
                'stock_request_in' => [['remarks_type', '=', StockRequestProduct::class], ['quantity', '>', 0]],
            ],
            groupBy: ['product_id'],
            whereClause: [['warehouse_id', '=', $warehouseId]]
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
            'transactions.quantity_stock_request_out',
            'transactions.value_stock_request_out',
            'transactions.quantity_stock_request_in',
            'transactions.value_stock_request_in',
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
            ->when($products, function ($query) use ($products) {
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
