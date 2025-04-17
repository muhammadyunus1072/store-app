<?php

namespace App\Repositories\Logistic\Transaction\ProductDetail;

use App\Helpers\Logistic\Stock\StockHandler;
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
        $destinationLocationId,
        $destinationLocationType,
        $destinationLocationNote,
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
            ->where('location_id', $destinationLocationId)
            ->where('location_type', $destinationLocationType)
            ->where('location_note', $destinationLocationType)
            ->where('entry_date', $entryDate)
            ->where('price', $price)
            ->where('code', $code)
            ->where('batch', $batch)
            ->where('expired_date', $expiredDate)
            ->first();

            logger('IN PRODUCT DETAIL');
            logger([
                'product_id' => $productId,
                'company_id' => $companyId,
                'location_id' => $destinationLocationId,
                'location_type' => $destinationLocationType,
                'location_note' => $destinationLocationNote,
                'entry_date' => $entryDate,
                'price' => $price,
                'code' => $code,
                'batch' => $batch,
                'expired_date' => $expiredDate,
                'remarks_id' => $remarksId,
                'remarks_type' => $remarksType,
                'remarks_note' => $remarksNote,
            ]);
        if (empty($productDetail)) {
            $productDetail = ProductDetailRepository::create([
                'product_id' => $productId,
                'company_id' => $companyId,
                'location_id' => $destinationLocationId,
                'location_type' => $destinationLocationType,
                'location_note' => $destinationLocationNote,
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
        $locationId,
        $locationType,
        $substractStockMethod
    ) {
        return ProductDetail::select(
            'id',
            'last_stock'
        )
            ->lockForUpdate()
            ->where('product_id', $productId)
            ->where('company_id', $companyId)
            ->where('location_id', $locationId)
            ->where('location_type', $locationType)
            ->where('last_stock', '>', 0)
            ->when($substractStockMethod == StockHandler::SUBSTRACT_STOCK_METHOD_FIFO, function ($query) {
                $query->orderBy('entry_date', 'ASC')->orderBy('id', 'ASC');
            })
            ->when($substractStockMethod == StockHandler::SUBSTRACT_STOCK_METHOD_LIFO, function ($query) {
                $query->orderBy('entry_date', 'DESC')->orderBy('id', 'DESC');
            })
            ->when($substractStockMethod == StockHandler::SUBSTRACT_STOCK_METHOD_FEFO, function ($query) {
                $query->orderBy('expired_date', 'ASC')->orderBy('id', 'ASC');
            })
            ->get();
    }
}
