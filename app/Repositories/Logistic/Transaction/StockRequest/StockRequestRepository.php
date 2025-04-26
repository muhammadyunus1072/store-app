<?php

namespace App\Repositories\Logistic\Transaction\StockRequest;

use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockRequest\StockRequest;

class StockRequestRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockRequest::class;
    }

    public static function datatable($dateStart, $dateEnd, $locationId, $locationType = Warehouse::class, $companyId)
    {
        return StockRequest::with('transactionStock')
            ->when($locationId, function ($query) use ($locationId, $locationType) {
                $query->where('destination_location_id', $locationId)
                ->where('destination_location_type', $locationType);
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('destination_company_id', $companyId);
            })
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"]);
        
    }
}
