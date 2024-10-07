<?php

namespace App\Repositories\Logistic\Transaction\ProductStockWarehouse;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\ProductStockWarehouse;

class ProductStockWarehouseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductStockWarehouse::class;
    }

    public static function createOrUpdate($productId, $warehouseId, $quantity)
    {
        $stock = ProductStockWarehouse::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($stock) {
            $stock->quantity = $quantity;
            $stock->save();
        } else {
            $stock = self::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
            ]);
        }

        return $stock;
    }
}
