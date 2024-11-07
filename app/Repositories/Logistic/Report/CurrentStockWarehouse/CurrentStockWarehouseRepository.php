<?php

namespace App\Repositories\Logistic\Report\CurrentStockWarehouse;

use Illuminate\Support\Facades\DB;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;

class CurrentStockWarehouseRepository
{
    public static function datatable($search, $date_start, $date_end, $product, $category_products, $warehouse_id)
    {
        $queryStockRowNumber = ProductDetailHistory::select(
            'product_detail_histories.product_detail_id',
            'product_detail_histories.last_stock',
            'product_detail_histories.remarks_type',
            'product_detail_histories.quantity',
            'product_details.warehouse_id',
            'product_details.product_id',
            'product_details.price',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY product_detail_histories.product_detail_id ORDER BY product_detail_histories.id DESC) as rn')
        )
            ->where('product_details.warehouse_id', '=', $warehouse_id)
            ->whereBetween('product_detail_histories.transaction_date', [$date_start." 00:00:00", $date_end." 23:59:59"])
            ->join('product_details', function($join)
            {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id')
                ->whereNull('product_details.deleted_at');
            });

        $lastStock = DB::table($queryStockRowNumber, "histories")
            ->select(
                'product_id',
                DB::raw('SUM(last_stock) as quantity'),
                DB::raw('SUM(last_stock * price) as value')
            )
            ->where('last_stock', '>', 0)
            ->where('rn', '=', 1)
            ->groupBy('product_id');

        $purchaseOrder = DB::table($queryStockRowNumber, "histories")
        ->select(
            'product_id',
            DB::raw('SUM(quantity) as quantity'),
            DB::raw('SUM(quantity * price) as value'),
        )
        ->where('remarks_type', '=', PurchaseOrderProduct::class)
        ->groupBy('product_id');

        $stockExpense = DB::table($queryStockRowNumber, "histories")
        ->select(
            'product_id',
            DB::raw('SUM(quantity) as quantity'),
            DB::raw('SUM(quantity * price) as value'),
        )
        ->where('remarks_type', '=', StockExpenseProduct::class)
        ->groupBy('product_id');

        $incomingTranfer = DB::table($queryStockRowNumber, "histories")
        ->select(
            'product_id',
            DB::raw('SUM(quantity) as quantity'),
            DB::raw('SUM(quantity * price) as value'),
        )
        ->where('remarks_type', '=', StockRequestProduct::class)
        ->where('quantity', '>', 0)
        ->groupBy('product_id');
        
        $outgoingTranfer = DB::table($queryStockRowNumber, "histories")
        ->select(
            'product_id',
            DB::raw('SUM(quantity) as quantity'),
            DB::raw('SUM(quantity * price) as value'),
        )
        ->where('remarks_type', '=', StockRequestProduct::class)
        ->where('quantity', '<', 0)
        ->groupBy('product_id');

        return Product::select(
            'products.name',
            'unit_details.name as unit_detail_name',
            'last_stock.quantity as last_stock',
            'last_stock.value as last_stock_value',
            'purchase_order.quantity as purchase_quantity',
            'purchase_order.value as purchase_value',
            'stock_expense.quantity as expense_quantity',
            'stock_expense.value as expense_value',
            'incoming_tranfer.quantity as incoming_tranfer_quantity',
            'incoming_tranfer.value as incoming_tranfer_value',
            'outgoing_tranfer.quantity as outgoing_tranfer_quantity',
            'outgoing_tranfer.value as outgoing_tranfer_value',
        )
        ->join('units', function ($join) {
            $join->on('units.id', '=', 'products.unit_id');
        })
        ->join('unit_details', function ($join) {
            $join->on('units.id', '=', 'unit_details.unit_id')->where('is_main', 1);
        })
        ->joinSub($lastStock, 'last_stock', function ($join) {
            $join->on('last_stock.product_id', '=', 'products.id');
        })
        ->leftJoinSub($purchaseOrder, 'purchase_order', function ($join) {
            $join->on('purchase_order.product_id', '=', 'products.id');
        })
        ->leftJoinSub($stockExpense, 'stock_expense', function ($join) {
            $join->on('stock_expense.product_id', '=', 'products.id');
        })
        ->leftJoinSub($incomingTranfer, 'incoming_tranfer', function ($join) {
            $join->on('incoming_tranfer.product_id', '=', 'products.id');
        })
        ->leftJoinSub($outgoingTranfer, 'outgoing_tranfer', function ($join) {
            $join->on('outgoing_tranfer.product_id', '=', 'products.id');
        })
        ->when($search, function($query) use($search)
        {
            $query->where('products.name', env('QUERY_LIKE'), '%' . $search . '%')
            ->orWhere('units.title', env('QUERY_LIKE'), '%' . $search . '%');
        })
        ->when($product, function($query) use($product)
        {
            $query->whereIn('products.id', $product);
        })
        ->when($category_products, function($query) use($category_products) {
            $query->whereHas('productCategories', function($query) use($category_products) {
                $query->whereIn('category_product_id', $category_products);
            });
        });
    }
}
