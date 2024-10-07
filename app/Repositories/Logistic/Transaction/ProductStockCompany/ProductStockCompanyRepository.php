<?php

namespace App\Repositories\Logistic\Transaction\ProductStockCompany;

use App\Models\Logistic\Transaction\ProductStockCompany;
use App\Repositories\MasterDataRepository;

class ProductStockCompanyRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductStockCompany::class;
    }

    public static function createOrUpdate($productId, $companyId, $quantity)
    {
        $stock = ProductStockCompany::where('product_id', $productId)
            ->where('company_id', $companyId)
            ->first();

        if ($stock) {
            $stock->quantity = $quantity;
            $stock->save();
        } else {
            $stock = self::create([
                'product_id' => $productId,
                'company_id' => $companyId,
                'quantity' => $quantity,
            ]);
        }

        return $stock;
    }
}
