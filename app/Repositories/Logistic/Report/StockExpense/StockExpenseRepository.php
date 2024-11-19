<?php

namespace App\Repositories\Logistic\Report\StockExpense;

use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;

class StockExpenseRepository
{
    public static function datatable($search, $dateStart, $dateEnd, $productIds, $categoryProductIds)
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
            ->whereBetween('stock_expenses.transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->when($productIds, function ($query) use ($productIds) {
                $query->whereIn('stock_expense_products.product_id', $productIds);
            })
            ->when($categoryProductIds, function ($query) use ($categoryProductIds) {
                $query->whereHas('product.productCategories', function ($query) use ($categoryProductIds) {
                    $query->whereIn('category_product_id', $categoryProductIds);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->orWhere('stock_expenses.number', env('QUERY_LIKE'), '%' . $search . '%');
                });
            });
    }
}
