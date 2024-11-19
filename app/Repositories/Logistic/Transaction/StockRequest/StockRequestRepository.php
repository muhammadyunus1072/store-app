<?php

namespace App\Repositories\Logistic\Transaction\StockRequest;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockRequest\StockRequest;

class StockRequestRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockRequest::class;
    }

    public static function datatable($dateStart, $dateEnd, $warehouseId, $companyId)
    {
        return StockRequest::with('transactionStock')
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('destination_warehouse_id', $warehouseId);
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('destination_company_id', $companyId);
            })
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"]);
    }
}
