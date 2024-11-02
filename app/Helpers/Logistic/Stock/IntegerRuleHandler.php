<?php

namespace App\Helpers\Logistic\Stock;

trait IntegerRuleHandler
{
    /*
    | Price = price + (savedValue/quantity) 
    | NOT LAST ITEM
    | Example Product A 9100.12 (10 Pcs)
    | - priceN = floor(9100.12) = 9100 (9 Pcs)
    | - priceRest = priceN + (9100.12 - priceN) * 10 = 9101.2 (1 Pcs)
    | - price1 = floor(priceRest) = 9101 (1 Pcs)
    | - savedValue = priceRest - price1 = 0.2
    |
    | Example Product A 9100.1 (10 Pcs)
    | - priceN = floor(9100.12) = 9100 (9 Pcs)
    | - priceRest = priceN + (9100.1 - priceN) * 10 = 9101 (1 Pcs)
    | - price1 = floor(priceRest) = 9101 (1 Pcs)
    | - savedValue = priceRest - price1 = 0
    |
    | LAST ITEM
    | Example Product A 9100.12 (10 Pcs)
    | - priceN = floor(9100.12) = 9100 (9 Pcs)
    | - priceRest = priceN + (9100.12 - priceN) * 10 = 9101.2 (1 Pcs)
    | - price1 = priceRest = 9101.2 (1 Pcs)
    |
    | Example Product A 9100.1 (10 Pcs)
    | - priceN = floor(9100.12) = 9100 (9 Pcs)
    | - priceRest = priceN + (9100.1 - priceN) * 10 = 9101 (1 Pcs)
    | - price1 = priceRest = 9101.2 (1 Pcs)
    */
    public static function convertProductsToIntegerRule($data)
    {
        $convertedData = [];
        $savedValue = 0;

        // Sort Ascending By Quantity
        usort($data, function ($a, $b) {
            return $a['quantity'] > $b['quantity'];
        });

        foreach ($data as $index => $item) {
            $resultConvert = StockHandler::convertUnitPrice($item['quantity'], $item['price'] + ($savedValue / $item['quantity']), $item['unit_detail_id']);

            if ($resultConvert['price'] - floor($resultConvert['price']) == 0) {
                $convertedData[] = [[
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'source_company_id' => $item['source_company_id'],
                    'source_warehouse_id' => $item['source_warehouse_id'],
                    'quantity' => $resultConvert['quantity'],
                    'unit_detail_id' => $resultConvert['unit_detail_id'],
                    'transaction_date' => $item['transaction_date'],
                    'price' => $resultConvert['price'],
                    'code' => $item['code'],
                    'batch' => $item['batch'],
                    'expired_date' => $item['expired_date'],
                    'remarks_id' => $item['remarks_id'],
                    'remarks_type' => $item['remarks_type'],
                    'remarks_note' => '-',
                ]];
                continue;
            }

            // HANDLE NOT INTEGER STOCK

            // Single Item
            if ($resultConvert['quantity'] == 1) {
                if ($index < count($data) - 1) {
                    // Not Last Item
                    $price = floor($resultConvert['price']);
                    $savedValue = $resultConvert['price'] - $price;
                } else {
                    // Last Item
                    $price = $resultConvert['price'];
                    $savedValue = 0;
                }

                $convertedData[] = [[
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'source_company_id' => $item['source_company_id'],
                    'source_warehouse_id' => $item['source_warehouse_id'],
                    'quantity' => $resultConvert['quantity'],
                    'unit_detail_id' => $resultConvert['unit_detail_id'],
                    'transaction_date' => $item['transaction_date'],
                    'price' => $price,
                    'code' => $item['code'],
                    'batch' => $item['batch'],
                    'expired_date' => $item['expired_date'],
                    'remarks_id' => $item['remarks_id'],
                    'remarks_type' => $item['remarks_type'],
                    'remarks_note' => '-',
                ]];
            }
            // Multiple Item
            else {
                $priceN = floor($resultConvert['price']);
                $priceRest = $priceN + ($resultConvert['price'] - $priceN) * $resultConvert['quantity'];

                if ($index < count($data) - 1) {
                    // Not Last Item
                    $price1 = floor($priceRest);
                    $savedValue = $priceRest - $price1;
                } else {
                    // Last Item
                    $price1 = $priceRest;
                    $savedValue = 0;
                }

                if ($price1 == $priceN) {
                    $convertedData[] = [[
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'source_company_id' => $item['source_company_id'],
                        'source_warehouse_id' => $item['source_warehouse_id'],
                        'quantity' => $resultConvert['quantity'],
                        'unit_detail_id' => $resultConvert['unit_detail_id'],
                        'transaction_date' => $item['transaction_date'],
                        'price' => $priceN,
                        'code' => $item['code'],
                        'batch' => $item['batch'],
                        'expired_date' => $item['expired_date'],
                        'remarks_id' => $item['remarks_id'],
                        'remarks_type' => $item['remarks_type'],
                        'remarks_note' => '-',
                    ]];
                } else {
                    $convertedData[] = [
                        [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'source_company_id' => $item['source_company_id'],
                            'source_warehouse_id' => $item['source_warehouse_id'],
                            'quantity' => $resultConvert['quantity'] - 1,
                            'unit_detail_id' => $resultConvert['unit_detail_id'],
                            'transaction_date' => $item['transaction_date'],
                            'price' => $priceN,
                            'code' => $item['code'],
                            'batch' => $item['batch'],
                            'expired_date' => $item['expired_date'],
                            'remarks_id' => $item['remarks_id'],
                            'remarks_type' => $item['remarks_type'],
                            'remarks_note' => 'N Unit',
                        ],
                        [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'source_company_id' => $item['source_company_id'],
                            'source_warehouse_id' => $item['source_warehouse_id'],
                            'quantity' => 1,
                            'unit_detail_id' => $resultConvert['unit_detail_id'],
                            'transaction_date' => $item['transaction_date'],
                            'price' => $price1,
                            'code' => $item['code'],
                            'batch' => $item['batch'],
                            'expired_date' => $item['expired_date'],
                            'remarks_id' => $item['remarks_id'],
                            'remarks_type' => $item['remarks_type'],
                            'remarks_note' => '1 Unit',
                        ]
                    ];
                }
            }
        }

        return $convertedData;
    }

    public static function integerRuleAdd($data)
    {
        $convertedData = self::convertProductsToIntegerRule($data);

        foreach ($convertedData as $groupProduct) {
            foreach ($groupProduct as $item) {
                StockHandler::createStock(
                    productId: $item['product_id'],
                    companyId: $item['source_company_id'],
                    warehouseId: $item['source_warehouse_id'],
                    transactionDate: $item['transaction_date'],
                    quantity: $item['quantity'],
                    price: $item['price'],
                    code: $item['code'],
                    batch: $item['batch'],
                    expiredDate: $item['expired_date'],
                    remarksId: $item['remarks_id'],
                    remarksType: $item['remarks_type'],
                    remarksNote: $item['remarks_note']
                );
            }
        }
    }
}
