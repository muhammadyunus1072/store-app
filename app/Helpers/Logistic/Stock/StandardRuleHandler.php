<?php

namespace App\Helpers\Logistic\Stock;

trait StandardRuleHandler
{
    public static function standardRuleAdd($data)
    {
        foreach ($data as $item) {
            $resultConvert = StockHandler::convertUnitPrice($item['quantity'], $item['price'], $item['unit_detail_id']);

            StockHandler::createStock(
                productId: $item['product_id'],
                companyId: $item['source_company_id'],
                warehouseId: $item['source_warehouse_id'],
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
}
