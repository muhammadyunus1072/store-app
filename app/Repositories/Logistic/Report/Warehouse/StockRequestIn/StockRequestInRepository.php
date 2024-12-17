<?php

namespace App\Repositories\Logistic\Report\Warehouse\StockRequestIn;

use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;

class StockRequestInRepository
{
    public static function datatable(
        $search,
        $dateStart,
        $dateEnd,
        $productIds,
        $categoryProductIds,
        $warehouseId,
        $warehouseIds,
    ) {
        return StockRequestProduct::select(
            'stock_requests.transaction_date',
            'stock_requests.number',
            'stock_requests.source_warehouse_name',
            'stock_requests.destination_warehouse_name',
            'stock_request_products.product_id',
            'stock_request_products.product_name',
            'stock_request_products.quantity',
            'stock_request_products.main_unit_detail_name',
            'stock_request_products.converted_quantity',
            'stock_request_products.unit_detail_name',
        )
            ->join('stock_requests', function ($join) {
                $join->on('stock_request_products.stock_request_id', '=', 'stock_requests.id')
                    ->whereNull('stock_requests.deleted_at');
            })
            ->where('stock_requests.source_warehouse_id', '=', $warehouseId)
            ->whereBetween('stock_requests.transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->when($warehouseIds, function ($query) use ($warehouseIds) {
                $query->whereIn('stock_request_products.destination_warehouse_id', $warehouseIds);
            })
            ->when($productIds, function ($query) use ($productIds) {
                $query->whereIn('stock_request_products.product_id', $productIds);
            })
            ->when($categoryProductIds, function ($query) use ($categoryProductIds) {
                $query->whereHas('product.productCategories', function ($query) use ($categoryProductIds) {
                    $query->whereIn('category_product_id', $categoryProductIds);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->orWhere('stock_requests.number', env('QUERY_LIKE'), '%' . $search . '%');
                });
            });
    }
}
