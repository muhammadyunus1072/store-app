<?php

namespace App\Repositories\Purchasing\Report\PurchaseOrder;

use Illuminate\Support\Facades\DB;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;

class PurchaseOrderRepository
{
    public static function datatable(
        $search,
        $dateStart,
        $dateEnd,
        $supplierIds
    ) {
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
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->when($supplierIds, function ($query) use ($supplierIds) {
                $query->whereIn('purchase_orders.supplier_id', $supplierIds);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function($query) use($search){
                    $query->orWhere('purchase_orders.number', env('QUERY_LIKE'), '%' . $search . '%');
                });
            });
    }
}
