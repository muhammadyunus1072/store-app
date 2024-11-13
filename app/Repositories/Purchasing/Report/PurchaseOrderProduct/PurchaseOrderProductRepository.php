<?php

namespace App\Repositories\Purchasing\Report\PurchaseOrderProduct;

use Illuminate\Support\Facades\DB;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;

class PurchaseOrderProductRepository
{
    public static function datatable($search, $date_start, $date_end, $products, $category_products, $supplier_id)
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
        ->join('purchase_orders', function($join)
        {
            $join->on('purchase_orders.id', '=', 'purchase_order_products.purchase_order_id')
            ->whereNull('purchase_orders.deleted_at');
        })
        ->when($supplier_id, function($query) use($supplier_id)
        {
            $query->where('purchase_orders.supplier_id', '=', $supplier_id);
        })
        ->when($search, function($query) use($search)
        {
            $query->orWhere('purchase_orders.number', env('QUERY_LIKE'), '%' . $search . '%')
            ->orWhere('purchase_orders.supplier_name', env('QUERY_LIKE'), '%' . $search . '%');
        })
        ->when($products, function($query) use($products)
        {
            $query->whereIn('purchase_order_products.product_id', $products);
        })
        ->when($category_products, function($query) use($category_products) {
            $query->whereHas('product.productCategories', function($query) use($category_products) {
                $query->whereIn('category_product_id', $category_products);
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
