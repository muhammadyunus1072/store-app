<?php

namespace App\Repositories\Purchasing\Report\PurchaseOrder;

use Illuminate\Support\Facades\DB;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;

class PurchaseOrderRepository
{
    public static function datatable($search, $date_start, $date_end, $supplier_id)
    {

        $queryPurchaseOrderProduct = PurchaseOrderProduct::select(
            'purchase_order_products.purchase_order_id',
            DB::raw('SUM(purchase_order_products.quantity * purchase_order_products.price) as value'),
        )
            ->groupBy('purchase_order_products.purchase_order_id');

        return PurchaseOrder::select(
            'purchase_orders.transaction_date',
            'purchase_orders.number',
            'purchase_orders.supplier_name',
            'purchase_order_products.value',
        )
        ->leftJoinSub($queryPurchaseOrderProduct, 'purchase_order_products', function ($join) {
            $join->on('purchase_order_products.purchase_order_id', '=', 'purchase_orders.id');
        })
        ->when($supplier_id, function($query) use($supplier_id)
        {
            $query->where('purchase_orders.supplier_id', '=', $supplier_id);
        })
        ->when($search, function($query) use($search)
        {
            $query->orWhere('purchase_orders.number', env('QUERY_LIKE'), '%' . $search . '%')
            ->orWhere('purchase_orders.supplier_name', env('QUERY_LIKE'), '%' . $search . '%');
        });
    }
}
