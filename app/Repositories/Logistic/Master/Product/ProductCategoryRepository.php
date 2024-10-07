<?php

namespace App\Repositories\Logistic\Master\Product;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Master\Product\ProductCategory;

class ProductCategoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductCategory::class;
    }

    public static function createIfNotExist($data)
    {
        $check = ProductCategory::where('product_id', $data['product_id'])
            ->where('category_product_id', $data['category_product_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function deleteExcept($productId, $categoryProductIds)
    {
        $deletedData = ProductCategory::where('product_id', $productId)
            ->whereNotIn('category_product_id', $categoryProductIds)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
