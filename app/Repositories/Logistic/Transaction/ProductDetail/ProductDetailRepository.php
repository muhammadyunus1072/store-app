<?php

namespace App\Repositories\Logistic\Transaction\ProductDetail;

use App\Helpers\Logistic\StockHelper;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\ProductDetail\ProductDetail;

class ProductDetailRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductDetail::class;
    }

    public static function createIfNotExist(
        $productId,
        $companyId,
        $warehouseId,
        $entryDate,
        $price,
        $code,
        $batch,
        $expiredDate,
        $remarksId = null,
        $remarksType = null,
        $remarksNote = null,
    ) {
        $productDetail = ProductDetail::where('product_id', $productId)
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->where('entry_date', $entryDate)
            ->where('price', $price)
            ->where('code', $code)
            ->where('batch', $batch)
            ->where('expired_date', $expiredDate)
            ->first();

        if (empty($productDetail)) {
            $productDetail = ProductDetailRepository::create([
                'product_id' => $productId,
                'company_id' => $companyId,
                'warehouse_id' => $warehouseId,
                'entry_date' => $entryDate,
                'price' => $price,
                'code' => $code,
                'batch' => $batch,
                'expired_date' => $expiredDate,
                'remarks_id' => $remarksId,
                'remarks_type' => $remarksType,
                'remarks_note' => $remarksNote,
            ]);
        }

        return $productDetail;
    }

    public static function getBySubstractMethod(
        $productId,
        $companyId,
        $warehouseId,
        $substractStockMethod
    ) {
        return ProductDetail::with('productStockDetail')
            ->where('product_id', $productId)
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->whereHas('productStockDetail', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->when($substractStockMethod == StockHelper::SUBSTRACT_STOCK_METHOD_FIFO, function ($query) {
                $query->orderBy('entry_date', 'ASC')->orderBy('id', 'ASC');
            })
            ->when($substractStockMethod == StockHelper::SUBSTRACT_STOCK_METHOD_LIFO, function ($query) {
                $query->orderBy('entry_date', 'DESC')->orderBy('id', 'DESC');
            })
            ->when($substractStockMethod == StockHelper::SUBSTRACT_STOCK_METHOD_FEFO, function ($query) {
                $query->orderBy('expired_date', 'ASC')->orderBy('id', 'ASC');
            })
            ->get();
    }
}
