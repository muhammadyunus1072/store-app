<?php

namespace App\Repositories\Logistic\Master\Product;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\Product\ProductUnit;

class ProductUnitRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductUnit::class;
    }

    public static function createIfNotExist($data)
    {
        $check = ProductUnit::where('product_id', $data['product_id'])
            ->where('category_product_id', $data['category_product_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function findMainUnit($unitId)
    {
        return ProductUnit::where('unit_id', '=', $unitId)->where('unit_detail_is_main', 1)->first();
    }

    public static function deleteOldUnit($productId)
    {
        $deletedData = ProductUnit::where('product_id', $productId)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
