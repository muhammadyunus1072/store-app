<?php

namespace App\Repositories\Purchasing\Master\Supplier;

use App\Repositories\MasterDataRepository;
use App\Models\Purchasing\Master\Supplier\SupplierCategory;

class SupplierCategoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return SupplierCategory::class;
    }

    public static function createIfNotExist($data)
    {
        $check = SupplierCategory::where('supplier_id', $data['supplier_id'])
            ->where('category_supplier_id', $data['category_supplier_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function deleteExcept($supplierId, $categorySupplierIds)
    {
        $deletedData = SupplierCategory::where('supplier_id', $supplierId)
            ->whereNotIn('category_supplier_id', $categorySupplierIds)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
