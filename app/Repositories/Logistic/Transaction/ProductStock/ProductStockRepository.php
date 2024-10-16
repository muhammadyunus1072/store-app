<?php

namespace App\Repositories\Logistic\Transaction\ProductStock;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\ProductStock\ProductStock;

class ProductStockRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductStock::class;
    }

    public static function createOrUpdate($productId, $quantity)
    {
        $productStock = ProductStock::where('product_id', $productId)->first();

        if ($productStock) {
            $productStock->quantity = $quantity;
            $productStock->save();
        } else {
            $productStock = self::create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $productStock;
    }
}
