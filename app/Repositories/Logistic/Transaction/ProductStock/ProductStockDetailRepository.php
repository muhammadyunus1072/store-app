<?php

namespace App\Repositories\Logistic\Transaction\ProductStock;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\ProductStock\ProductStockDetail;

class ProductStockDetailRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductStockDetail::class;
    }

    public static function createOrUpdate($productDetailId, $quantity)
    {
        $stock = ProductStockDetail::where('product_detail_id', $productDetailId)->first();

        if ($stock) {
            $stock->quantity = $quantity;
            $stock->save();
        } else {
            $stock = self::create([
                'product_detail_id' => $productDetailId,
                'quantity' => $quantity,
            ]);
        }

        return $stock;
    }
}
