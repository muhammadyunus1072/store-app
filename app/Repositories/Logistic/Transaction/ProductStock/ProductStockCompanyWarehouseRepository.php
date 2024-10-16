<?php

namespace App\Repositories\Logistic\Transaction\ProductStock;

use App\Models\Logistic\Transaction\ProductStock\ProductStockCompanyWarehouse;
use App\Repositories\MasterDataRepository;

class ProductStockCompanyWarehouseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductStockCompanyWarehouse::class;
    }

    public static function createOrUpdate($productId, $companyId, $warehouseId, $quantity)
    {
        $stock = ProductStockCompanyWarehouse::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('company_id', $companyId)
            ->first();

        if ($stock) {
            $stock->quantity = $quantity;
            $stock->save();
        } else {
            $stock = self::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'company_id' => $companyId,
                'quantity' => $quantity,
            ]);
        }

        return $stock;
    }
}
