<?php

namespace App\Repositories\Core\Company;

use App\Repositories\MasterDataRepository;
use App\Models\Core\Company\CompanyWarehouse;

class CompanyWarehouseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return CompanyWarehouse::class;
    }

    public static function createIfNotExist($data)
    {
        $check = CompanyWarehouse::where('company_id', $data['company_id'])
            ->where('warehouse_id', $data['warehouse_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function deleteExcept($company_id, $warehouseIds)
    {
        $deletedData = CompanyWarehouse::where('company_id', $company_id)
            ->whereNotIn('warehouse_id', $warehouseIds)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
