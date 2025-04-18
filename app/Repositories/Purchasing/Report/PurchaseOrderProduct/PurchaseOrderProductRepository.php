<?php

namespace App\Repositories\Purchasing\Report\PurchaseOrderProduct;

use Illuminate\Support\Facades\DB;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;

class PurchaseOrderProductRepository
{
    public static function datatable($search, $dateStart, $dateEnd, $productIds, $categoryProductIds, $supplierIds)
    {

        return PurchaseOrderProduct::select(
            'purchase_order_products.purchase_order_id',
            'purchase_orders.transaction_date',
            'purchase_orders.number',
            'purchase_orders.supplier_name',
            'purchase_order_products.product_name',
            'purchase_order_products.quantity',
            'purchase_order_products.unit_detail_name',
            'purchase_order_products.converted_quantity',
            'purchase_order_products.main_unit_detail_name',
            DB::raw('SUM(purchase_order_products.quantity * purchase_order_products.price) as value'),
        )
            ->join('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', '=', 'purchase_order_products.purchase_order_id')
                    ->whereNull('purchase_orders.deleted_at');
            })
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->when($supplierIds, function ($query) use ($supplierIds) {
                $query->whereIn('purchase_orders.supplier_id', $supplierIds);
            })
            ->when($productIds, function ($query) use ($productIds) {
                $query->whereIn('purchase_order_products.product_id', $productIds);
            })
            ->when($categoryProductIds, function ($query) use ($categoryProductIds) {
                $query->whereHas('product.productCategories', function ($query) use ($categoryProductIds) {
                    $query->whereIn('category_product_id', $categoryProductIds);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->orWhere('purchase_orders.number', env('QUERY_LIKE'), '%' . $search . '%');
                });
            })
            ->groupBy(
                'purchase_order_products.purchase_order_id',
                'purchase_orders.transaction_date',
                'purchase_orders.number',
                'purchase_orders.supplier_name',
                'purchase_order_products.product_name',
                'purchase_order_products.quantity',
                'purchase_order_products.unit_detail_name',
                'purchase_order_products.converted_quantity',
                'purchase_order_products.main_unit_detail_name',
            );
    }
}
