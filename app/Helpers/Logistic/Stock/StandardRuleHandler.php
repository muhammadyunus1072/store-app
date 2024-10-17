<?php

namespace App\Helpers\Logistic\Stock;

use App\Helpers\ErrorMessageHelper;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;

trait StandardRuleHandler
{
    public static function standardRuleAdd($data, $isStockValueIncludeTaxPpn)
    {
        foreach ($data as $item) {
            $price = $item['price'];
            if ($isStockValueIncludeTaxPpn) {
                $price *= (100 + $item['tax_value']) / 100.0;
            }

            $resultConvert = StockHandler::convertUnitPrice($item['quantity'], $price, $item['unit_detail_id']);

            StockHandler::createStock(
                productId: $item['product_id'],
                companyId: $item['company_id'],
                warehouseId: $item['warehouse_id'],
                transactionDate: $item['transaction_date'],
                quantity: $resultConvert['quantity'],
                price: $resultConvert['price'],
                code: $item['code'],
                batch: $item['batch'],
                expiredDate: $item['expired_date'],
                remarksId: $item['remarks_id'],
                remarksType: $item['remarks_type']
            );
        }
    }

    public static function standardRuleUpdateAdd($data, $isStockValueIncludeTaxPpn)
    {
        foreach ($data as $item) {
            $price = $item['price'];
            if ($isStockValueIncludeTaxPpn) {
                $price *= (100 + $item['tax_value']) / 100.0;
            }

            $resultConvert = StockHandler::convertUnitPrice($item['quantity'], $price, $item['unit_detail_id']);

            $history = ProductDetailHistoryRepository::findBy(whereClause: [
                ['remarks_id', $item['remarks_id']],
                ['remarks_type', $item['remarks_type']],
            ]);

            // Update Information
            ProductDetailRepository::update($history->product_detail_id, [
                'entry_date' => $item['transaction_date'],
                'expired_date' => $item['expired_date'],
                'batch' => $item['batch'],
                'price' => $resultConvert['price'],
                'code' => $item['code'],
            ]);

            // Update History
            ProductDetailHistoryRepository::update($history->id, [
                'transaction_date' => $item['transaction_date'],
                'quantity' => $resultConvert['quantity'],
            ]);

            // Confirm Stock
            if (StockHandler::getStockDetail($history->product_detail_id) < 0) {
                throw new \Exception(ErrorMessageHelper::stockNotAvailable($item['product_name']));
            }
        }
    }
}
