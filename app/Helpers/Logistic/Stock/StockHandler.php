<?php

namespace App\Helpers\Logistic\Stock;

use App\Helpers\General\ErrorMessageHelper;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;
use App\Settings\SettingLogistic;

class StockHandler
{
    use StandardRuleHandler, IntegerRuleHandler;

    const SUBSTRACT_STOCK_METHOD_FIFO = 'FIFO';
    const SUBSTRACT_STOCK_METHOD_LIFO = 'LIFO';
    const SUBSTRACT_STOCK_METHOD_FEFO = 'FEFO';

    const SUBSTRACT_STOCK_METHOD_CHOICE = [
        self::SUBSTRACT_STOCK_METHOD_FIFO => "First In First Out (FIFO)",
        self::SUBSTRACT_STOCK_METHOD_LIFO => "Last In First Out (LIFO)",
        self::SUBSTRACT_STOCK_METHOD_FEFO => "First Expired First Out (FEFO)",
    ];

    public static function convertUnitPrice(
        $quantity,
        $price,
        $fromUnitDetailId
    ) {
        $fromUnitDetail = UnitDetailRepository::find($fromUnitDetailId);

        if (empty($targetUnitDetailId)) {
            $targetUnitDetail = UnitDetailRepository::findMainUnit($fromUnitDetail->unit_id);
        } else {
            $targetUnitDetail = UnitDetailRepository::find($targetUnitDetailId);
        }

        return [
            'quantity' => $quantity * $fromUnitDetail->value / $targetUnitDetail->value,
            'price' => $price * $targetUnitDetail->value / $fromUnitDetail->value,
            'unit_detail_id' => $targetUnitDetail->id,
            'unit_detail_name' => $targetUnitDetail->name,
        ];
    }

    public static function createStock(
        $productId,
        $companyId,
        $warehouseId,
        $transactionDate,
        $quantity,
        $price,
        $code,
        $batch,
        $expiredDate,
        $remarksId = null,
        $remarksType = null,
        $remarksNote = null,
    ) {
        $productDetail = ProductDetailRepository::createIfNotExist(
            productId: $productId,
            companyId: $companyId,
            warehouseId: $warehouseId,
            entryDate: $transactionDate,
            price: $price,
            code: $code,
            batch: $batch,
            expiredDate: $expiredDate,
            remarksId: $remarksId,
            remarksType: $remarksType,
            remarksNote: $remarksNote,
        );

        ProductDetailHistoryRepository::create([
            'product_detail_id' => $productDetail->id,
            'transaction_date' => $transactionDate,
            'quantity' => $quantity,
            'remarks_id' => $remarksId,
            'remarks_type' => $remarksType,
            'remarks_note' => $remarksNote,
        ]);
    }

    /*
    | TRANSACTION STOCK
    */
    public static function add($data)
    {
        if (count($data) == 0) {
            return;
        }

        if (SettingLogistic::get(SettingLogistic::PRICE_INTEGER_VALUE)) {
            self::integerRuleAdd($data);
        } else {
            self::standardRuleAdd($data);
        }
    }

    public static function substract($data)
    {
        if (count($data) == 0) {
            return;
        }

        $createdHistories = [];

        foreach ($data as $item) {
            $resultConvert = self::convertUnitPrice($item['quantity'], 0, $item['unit_detail_id']);
            $substractQty = $resultConvert['quantity'];

            // Substract Stock Process
            $productDetails = ProductDetailRepository::getBySubstractMethod(
                productId: $item['product_id'],
                companyId: $item['source_company_id'],
                warehouseId: $item['source_warehouse_id'],
                substractStockMethod: SettingLogistic::get(SettingLogistic::SUBSTRACT_STOCK_METHOD)
            );

            foreach ($productDetails as $productDetail) {
                $usedQty = min($productDetail->last_stock, $substractQty) * -1;

                $createdHistories[$item['id']][] = ProductDetailHistoryRepository::create([
                    'product_detail_id' => $productDetail->id,
                    'transaction_date' => $item['transaction_date'],
                    'quantity' => $usedQty,
                    'remarks_id' => $item['remarks_id'],
                    'remarks_type' => $item['remarks_type'],
                ]);

                $substractQty += $usedQty;

                if ($substractQty == 0) {
                    break;
                }
            }

            if ($substractQty > 0) {
                throw new \Exception(ErrorMessageHelper::stockNotAvailable(
                    productName: $item['product_name'],
                    unitName: $resultConvert['unit_detail_name'],
                    stock: $resultConvert['quantity'] - $substractQty,
                    quantity: $item['quantity']
                ));
            }
        }

        return $createdHistories;
    }

    public static function transfer($data)
    {
        if (count($data) == 0) {
            return;
        }

        $createdHistories = self::substract($data);

        foreach ($data as $item) {
            foreach ($createdHistories[$item['id']] as $history) {
                self::createStock(
                    productId: $item['product_id'],
                    companyId: $item['destination_company_id'],
                    warehouseId: $item['destination_warehouse_id'],
                    transactionDate: $item['transaction_date'],
                    quantity: abs($history->quantity),
                    price: $history->productDetail->price,
                    code: $history->productDetail->code,
                    batch: $history->productDetail->batch,
                    expiredDate: $history->productDetail->expired_date,
                    remarksId: $item['remarks_id'],
                    remarksType: $item['remarks_type'],
                    remarksNote: $history->id,
                );
            }
        }
    }

    public static function cancel($data)
    {
        if (count($data) == 0) {
            return;
        }

        foreach ($data as $item) {
            $whereClause = [
                ['remarks_id', $item['remarks_id']],
                ['remarks_type', $item['remarks_type']]
            ];

            if (isset($item['remarks_note'])) {
                $whereClause[] = ['remarks_note', $item['remarks_note']];
            }

            // Delete Histories
            ProductDetailHistoryRepository::deleteBy($whereClause);
        }
    }

    /*
    | CALCULATE CURRENT STOCK
    */
    public static function getStockByProductDetail(
        $productDetailId,
        $thresholdDate = null,
    ) {
        if ($thresholdDate == null) {
            // Last Stock (Source : Product Detail)
            $productDetail = ProductDetailRepository::find($productDetailId);
            return $productDetail ? $productDetail->last_stock : 0;
        } else {
            // Last Stock Based On Date (Source : Product Detail Histories)
            $lastHistory = ProductDetailHistoryRepository::findLastHistory($productDetailId, $thresholdDate);
            return $lastHistory ? $lastHistory->last_stock : 0;
        }
    }

    public static function getStock(
        $productDetailId = null,
        $productId,
        $companyId,
        $warehouseId,
        $thresholdDate = null,
    ) {
        if ($thresholdDate == null) {
            // Last Stock (Source : Product Detail)
            $productDetails = ProductDetailRepository::getBy([
                ['product_id', $productId],
                ['company_id', $companyId],
                ['warehouse_id', $warehouseId]
            ]);
            return $productDetails->sum('last_stock');
        } else {
            // Last Stock Based On Date (Source : Product Detail Histories)
            $histories = ProductDetailHistoryRepository::getLastHistories($productDetailId, $productId, $companyId, $warehouseId, $thresholdDate);
            return $histories->sum('last_stock');
        }
    }
}
