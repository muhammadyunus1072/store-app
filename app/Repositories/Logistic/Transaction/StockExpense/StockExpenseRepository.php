<?php

namespace App\Repositories\Logistic\Transaction\StockExpense;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockExpense\StockExpense;

class StockExpenseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockExpense::class;
    }

    public static function findWithDetails($id)
    {
        return StockExpense::with([
            'warehouse',    
            'stockExpenseProducts',    
        ]
        )->where('id', $id)->first();
    }

    public static function datatable()
    {
        return StockExpense::query();
    }
}
