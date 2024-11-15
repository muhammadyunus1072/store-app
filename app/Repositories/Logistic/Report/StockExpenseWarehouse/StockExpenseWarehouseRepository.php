<?php

namespace App\Repositories\Logistic\Report\StockExpenseWarehouse;

use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;

class StockExpenseWarehouseRepository
{
    public static function datatable(
        $search,
        $dateStart,
        $dateEnd,
        $products,
        $categoryProducts,
        $warehouse_id
    ) {

        return StockExpenseProduct::select(
            'stock_expenses.transaction_date',
            'stock_expenses.number',
            'stock_expenses.warehouse_name',
            'stock_expense_products.product_id',
            'stock_expense_products.product_name',
            'stock_expense_products.quantity',
            'stock_expense_products.main_unit_detail_name',
            'stock_expense_products.converted_quantity',
            'stock_expense_products.unit_detail_name',
        )
            ->join('stock_expenses', function ($join) {
                $join->on('stock_expense_products.stock_expense_id', '=', 'stock_expenses.id')
                    ->whereNull('stock_expenses.deleted_at');
            })
            ->where('stock_expenses.warehouse_id', '=', $warehouse_id)
            ->whereBetween('stock_expenses.transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->when($products, function ($query) use ($products) {
                $query->whereIn('stock_expense_products.product_id', $products);
            })
            ->when($categoryProducts, function ($query) use ($categoryProducts) {
                $query->whereHas('product.productCategories', function ($query) use ($categoryProducts) {
                    $query->whereIn('category_product_id', $categoryProducts);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where('stock_expense_products.product_name', env('QUERY_LIKE'), '%' . $search . '%')
                    ->orWhere('stock_expenses.number', env('QUERY_LIKE'), '%' . $search . '%');
            });
    }
}
