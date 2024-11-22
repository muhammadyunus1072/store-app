<?php

namespace App\Repositories\Logistic\Report\All\StockExpired;

use App\Models\Logistic\Master\Product\Product;
use Illuminate\Support\Facades\DB;

class StockExpiredRepository
{
    public static function datatable(
        $search,
        $expiredDateStart,
        $expiredDateEnd,
        $productIds,
        $categoryProductIds
    ) {
        return Product::select(
            'products.name',
            'unit_details.name as unit_detail_name',
            'product_details.price',
            'product_details.entry_date',
            'product_details.code',
            'product_details.batch',
            'product_details.expired_date',
            DB::raw('SUM(product_details.last_stock) as stock_qty'),
            DB::raw('SUM(product_details.last_stock * product_details.price) as stock_value'),
        )
            ->join('product_details', function ($join) {
                $join->on('product_details.product_id', '=', 'products.id')
                    ->whereNull('product_details.deleted_at');
            })
            ->join('units', function ($join) {
                $join->on('units.id', '=', 'products.unit_id')
                    ->whereNull('units.deleted_at');
            })
            ->join('unit_details', function ($join) {
                $join->on('units.id', '=', 'unit_details.unit_id')
                    ->where('unit_details.is_main', 1)
                    ->whereNull('unit_details.deleted_at');
            })
            ->when(!empty($productIds), function ($query) use ($productIds) {
                $query->whereIn('products.id', $productIds);
            })
            ->when($categoryProductIds, function ($query) use ($categoryProductIds) {
                $query->whereHas('productCategories', function ($query) use ($categoryProductIds) {
                    $query->whereIn('category_product_id', $categoryProductIds);
                });
            })
            ->when($expiredDateStart, function ($query) use ($expiredDateStart) {
                $query->where('product_details.expired_date', '>=', $expiredDateStart);
            })
            ->when($expiredDateEnd, function ($query) use ($expiredDateEnd) {
                $query->where('product_details.expired_date', '<=', $expiredDateEnd);
            })
            ->where('products.type', '=', Product::TYPE_PRODUCT_WITH_STOCK)
            ->where('product_details.last_stock', '>', 0)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->orWhere('products.name', env('QUERY_LIKE'), '%' . $search . '%');
                });
            })
            ->groupBy(
                'product_details.price',
                'product_details.entry_date',
                'product_details.code',
                'product_details.batch',
                'product_details.expired_date',
                'products.name',
                'unit_details.name',
            );
    }
}
