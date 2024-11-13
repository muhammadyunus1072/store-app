<?php

namespace App\Repositories\Logistic\Report\HistoryStockDetailWarehouse;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;

class HistoryStockDetailWarehouseRepository
{
    public static function datatable($search, $date_start, $date_end, $products, $category_products, $warehouse_id)
    {
        $query = ProductDetailHistory::select(
            'product_detail_histories.id',
            'product_detail_histories.remarks_id',
            'product_detail_histories.remarks_type',
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
            ->join('products', function ($join) {
                $join->on('products.id', '=', 'product_details.product_id');
            })
            ->join('units', function ($join) {
                $join->on('products.unit_id', '=', 'units.id');
            })
            ->join('unit_details', function ($join) {
                $join->on('units.id', '=', 'unit_details.unit_id')->where('is_main', 1);
            })
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'product_details.company_id');
            })
            ->join('warehouses', function ($join) {
                $join->on('warehouses.id', '=', 'product_details.warehouse_id');
            })
            ->where('product_details.warehouse_id', '=', $warehouse_id)
            ->whereBetween('product_detail_histories.transaction_date', [$date_start." 00:00:00", $date_end." 23:59:59"])
            ->when($search, function($query) use($search)
            {
                $query->where('products.name', env('QUERY_LIKE'), '%' . $search . '%')
                ->orWhere('units.title', env('QUERY_LIKE'), '%' . $search . '%');
            })
            ->when($products, function($query) use($products)
            {
                $query->whereIn('products.id', $products);
            })
            ->when($category_products, function($query) use($category_products) {
                $query->whereHas('productDetail.product.productCategories', function($query) use($category_products) {
                    $query->whereIn('category_product_id', $category_products);
                });
            });

        return $query;
    }
}
