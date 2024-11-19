<?php

namespace App\Repositories\Logistic\Transaction\StockExpense;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockExpense\StockExpense;

class StockExpenseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockExpense::class;
    }

    public static function datatable($dateStart, $dateEnd, $warehouseId, $companyId)
    {
        return StockExpense::with('transactionStock')
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"]);
    }
}
