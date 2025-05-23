<?php

namespace App\Helpers\Logistic\Stock;

use App\Helpers\General\NumberFormatter;
use App\Models\Logistic\Master\DisplayRack\DisplayRack;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Repositories\Logistic\Master\Product\ProductUnitRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;
use App\Settings\SettingLogistic;
use Carbon\Carbon;

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

    /*
    | TRANSACTION STOCK
    */
    public static function createStock(
        $productId,
        $companyId,
        $locationId,
        $locationType,
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
            destinationLocationId: $locationId,
            destinationLocationType: $locationType,
            destinationLocationNote: $locationType,
            entryDate: $transactionDate,
            price: $price,
            code: $code,
            batch: $batch,
            expiredDate: $expiredDate,
            remarksId: $remarksId,
            remarksType: $remarksType,
            remarksNote: $remarksNote,
        );

        logger('ifnotexit');
        logger($productDetail);

        $a = ProductDetailHistoryRepository::create([
            'product_detail_id' => $productDetail->id,
            'transaction_date' => $transactionDate,
            'quantity' => $quantity,
            'remarks_id' => $remarksId,
            'remarks_type' => $remarksType,
            'remarks_note' => $remarksNote,
        ]);

        logger('a');
        logger($a);
    }


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

        foreach ($data as $index => $item) {
            $resultConvert = self::convertUnitPrice($item['quantity'], 0, $item['unit_detail_id']);
            $substractQty = $resultConvert['quantity'];

            // Substract Stock Process
            $productDetails = ProductDetailRepository::getBySubstractMethod(
                productId: $item['product_id'],
                companyId: $item['source_company_id'],
                locationId: $item['source_warehouse_id'],
                locationType: Warehouse::class,
                substractStockMethod: SettingLogistic::get(SettingLogistic::SUBSTRACT_STOCK_METHOD)
            );

            logger('sub p');
            logger($productDetails);

            foreach ($productDetails as $productDetail) {
                $usedQty = min($productDetail->last_stock, $substractQty) * -1;

                $createdHistories[$index][] = ProductDetailHistoryRepository::create([
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

            // HANDLE: NOT ENOUGH STOCK
            if ($substractQty > 0) {
                $productName = $item['product_name'];
                $unitName = $resultConvert['unit_detail_name'];
                $strStock = NumberFormatter::format($resultConvert['quantity'] - $substractQty);
                $strQty = NumberFormatter::format($resultConvert['quantity']);

                throw new \Exception("Stock {$productName} Tidak Mencukupi. Tersedia {$strStock} {$unitName} dan yang dibutuhkan {$strQty} {$unitName}.");
            }
        }

        return $createdHistories;
    }

    public static function sales($data)
    {
        if (count($data) == 0) {
            return;
        }

        $createdHistories = [];

        foreach ($data as $index => $item) {
            $resultConvert = self::convertUnitPrice($item['quantity'], 0, $item['unit_detail_id']);
            $substractQty = $resultConvert['quantity'];

            // Substract Stock Process
            $productDetails = ProductDetailRepository::getBySubstractMethod(
                productId: $item['product_id'],
                companyId: $item['source_company_id'],
                locationId: $item['source_warehouse_id'],
                locationType: DisplayRack::class,
                substractStockMethod: SettingLogistic::get(SettingLogistic::SUBSTRACT_STOCK_METHOD)
            );

            logger('sub p');
            logger($productDetails);

            foreach ($productDetails as $productDetail) {
                $usedQty = min($productDetail->last_stock, $substractQty) * -1;

                $createdHistories[$index][] = ProductDetailHistoryRepository::create([
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

            // HANDLE: NOT ENOUGH STOCK
            if ($substractQty > 0) {
                $productName = $item['product_name'];
                $unitName = $resultConvert['unit_detail_name'];
                $strStock = NumberFormatter::format($resultConvert['quantity'] - $substractQty);
                $strQty = NumberFormatter::format($resultConvert['quantity']);

                throw new \Exception("Stock {$productName} Tidak Mencukupi. Tersedia {$strStock} {$unitName} dan yang dibutuhkan {$strQty} {$unitName}.");
            }
        }

        return $createdHistories;
    }

    public static function opname($data)
    {
        if (count($data) == 0) {
            return;
        }

        $createdHistories = [];

        foreach ($data as $index => $item) {
            $resultConvert = self::convertUnitPrice($item['quantity'], 0, $item['unit_detail_id']);
            $substractQty = $resultConvert['quantity'];

            // Substract Stock Process
            $productDetails = ProductDetailRepository::getBySubstractMethod(
                productId: $item['product_id'],
                companyId: $item['source_company_id'],
                locationId: $item['source_warehouse_id'],
                locationType: Warehouse::class,
                substractStockMethod: SettingLogistic::get(SettingLogistic::SUBSTRACT_STOCK_METHOD)
            );

            logger('sub opname');
            logger($productDetails);

            foreach ($productDetails as $productDetail) {
                $usedQty = min($productDetail->last_stock, $substractQty);

                $createdHistories[$index][] = ProductDetailHistoryRepository::create([
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
        }

        return $createdHistories;
    }

    public static function transfer($data)
    {
        if (count($data) == 0) {
            return;
        }

        $createdHistories = self::substract($data);

        foreach ($data as $index => $item) {
            foreach ($createdHistories[$index] as $history) {
                self::createStock(
                    productId: $item['product_id'],
                    companyId: $item['destination_company_id'],
                    locationId: $item['destination_location_id'],
                    locationType: $item['destination_location_type'],
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
        $transactionDate,
    ) {
        if ($transactionDate == Carbon::now()->format("Y-m-d")) {
            // Last Stock (Source : Product Detail)
            $productDetail = ProductDetailRepository::find($productDetailId);
            return $productDetail ? $productDetail->last_stock : 0;
        } else {
            // Last Stock Based On Date (Source : Product Detail Histories)
            $lastHistory = ProductDetailHistoryRepository::findLastHistory($productDetailId, $transactionDate);
            return $lastHistory ? $lastHistory->last_stock : 0;
        }
    }

    public static function getStock(
        $productId,
        $companyId,
        $warehouseId,
        $transactionDate,
    ) {
        if ($transactionDate == Carbon::now()->format("Y-m-d")) {
            // Last Stock (Source : Product Detail)
            $productDetails = ProductDetailRepository::getBy([
                ['product_id', $productId],
                ['company_id', $companyId],
                ['location_id', $warehouseId],
                ['location_type', Warehouse::class]
            ]);
            return $productDetails->sum('last_stock');
        } else {
            // Last Stock Based On Date (Source : Product Detail Histories)
            $histories = ProductDetailHistoryRepository::getLastHistories($productId, $transactionDate, $companyId, $warehouseId);
            return $histories->sum('last_stock');
        }
    }

    public static function convertProductUnitPrice(
        $quantity,
        $price,
        $fromUnitDetailId,
        $targetUnitDetailId = null,
    ) {
        $fromUnitDetail = ProductUnitRepository::find($fromUnitDetailId);

        if (!$targetUnitDetailId) {
            $targetUnitDetail = ProductUnitRepository::findMainUnit($fromUnitDetail->unit_id);
        } else {
            $targetUnitDetail = ProductUnitRepository::find($targetUnitDetailId);
        }

        return [
            'quantity' => $quantity * $fromUnitDetail->unit_detail_value / $targetUnitDetail->unit_detail_value,
            'price' => $price * $targetUnitDetail->unit_detail_value / $fromUnitDetail->unit_detail_value,
            'product_unit_detail_id' => $targetUnitDetail->product_unit_detail_id,
            'product_unit_detail_name' => $targetUnitDetail->product_unit_detail_name,
        ];
    }

    public static function convertUnitPrice(
        $quantity,
        $price,
        $fromUnitDetailId,
        $targetUnitDetailId = null,
    ) {
        $fromUnitDetail = UnitDetailRepository::find($fromUnitDetailId);

        if (!$targetUnitDetailId) {
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

    public static function getStockAvailablity(
        $productId,
        $companyId,
        $warehouseId,
        $qtyToBeUsed,
        $unitDetailId,
        $transactionDate
    ) {
        $currentStock = self::getStock($productId, $companyId, $warehouseId, $transactionDate);
        $convertResult = self::convertUnitPrice($qtyToBeUsed, 0, $unitDetailId);

        return [
            'is_stock_available' => $convertResult['quantity'] <= $currentStock,
            'current_stock' => $currentStock,
            'quantity_to_be_used' => $convertResult['quantity'],
            'unit_detail_name' => $convertResult['unit_detail_name'],
        ];
    }
}
