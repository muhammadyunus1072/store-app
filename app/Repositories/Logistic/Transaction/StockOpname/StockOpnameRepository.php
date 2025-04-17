<?php

namespace App\Repositories\Logistic\Transaction\StockOpname;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockOpname\StockOpname;

class StockOpnameRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockOpname::class;
    }

    public static function datatable($dateStart, $dateEnd, $locationId, $locationType, $companyId)
    {
        return StockOpname::with('transactionStock')
            ->when($locationId, function ($query) use ($locationId, $locationType) {
                $query->where('location_id', $locationId)
                    ->where('location_type', $locationType);
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->whereBetween('stock_opname_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"]);
    }

    public static function deleteWithEmptyProducts()
    {
        $data = StockOpname::whereDoesntHave('stockOpnameProducts')->get();
        foreach ($data as $item) {
            $item->delete();
        }
    }
}
