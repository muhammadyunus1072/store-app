<?php

namespace App\Repositories\Logistic\Report\StockExpense;

use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;

class StockExpenseRepository
{
    public static function datatable($search, $date_start, $date_end, $products, $category_products)
    {

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
        ->whereBetween('stock_expenses.transaction_date', [$date_start." 00:00:00", $date_end." 23:59:59"])
        ->when($search, function($query) use($search)
        {
            $query->where('stock_expense_products.product_name', env('QUERY_LIKE'), '%' . $search . '%')
            ->orWhere('stock_expenses.number', env('QUERY_LIKE'), '%' . $search . '%')
            ->orWhere('stock_expense_products.main_unit_detail_name', env('QUERY_LIKE'), '%' . $search . '%')
            ->orWhere('stock_expense_products.unit_detail_name', env('QUERY_LIKE'), '%' . $search . '%');
        })
        ->when($products, function($query) use($products)
        {
            $query->whereIn('stock_expense_products.product_id', $products);
        })
        ->when($category_products, function($query) use($category_products) {
            $query->whereHas('product.productCategories', function($query) use($category_products) {
                $query->whereIn('category_product_id', $category_products);
            });
        });
    }
}
