<?php

namespace App\Repositories\Purchasing\Report\PurchaseOrderProduct;

use Illuminate\Support\Facades\DB;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;

class PurchaseOrderProductRepository
{
    public static function datatable($search, $dateStart, $dateEnd, $products, $categoryProducts, $supplierIds)
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
            ->when($products, function ($query) use ($products) {
                $query->whereIn('purchase_order_products.product_id', $products);
            })
            ->when($categoryProducts, function ($query) use ($categoryProducts) {
                $query->whereHas('product.productCategories', function ($query) use ($categoryProducts) {
                    $query->whereIn('category_product_id', $categoryProducts);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->orWhere('purchase_orders.number', env('QUERY_LIKE'), '%' . $search . '%')
                    ->orWhere('purchase_orders.supplier_name', env('QUERY_LIKE'), '%' . $search . '%');
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
