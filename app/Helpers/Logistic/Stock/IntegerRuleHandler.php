<?php

namespace App\Helpers\Logistic\Stock;

use App\Helpers\General\ErrorMessageHelper;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;

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
                $convertedData[$item['id']] = [[
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'company_id' => $item['company_id'],
                    'warehouse_id' => $item['warehouse_id'],
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

                $convertedData[$item['id']] = [[
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'company_id' => $item['company_id'],
                    'warehouse_id' => $item['warehouse_id'],
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
                    $convertedData[$item['id']] = [[
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'company_id' => $item['company_id'],
                        'warehouse_id' => $item['warehouse_id'],
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
                    $convertedData[$item['id']] = [
                        [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'company_id' => $item['company_id'],
                            'warehouse_id' => $item['warehouse_id'],
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
                            'company_id' => $item['company_id'],
                            'warehouse_id' => $item['warehouse_id'],
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
                    companyId: $item['company_id'],
                    warehouseId: $item['warehouse_id'],
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

    /*
    | EXPLANATION
    | o Case 2 Stock Type => 2 Stock Type
    | Solusi : 
    | 1.   Langsung melakukan update sesuai dengan remarks_note
    |
    | o Case 1 Stock Type => 2 Stock Type
    | Solusi : 
    | 1.   Perubahan jumlah dan informasi serta remarks_note dari '-' berubah menjadi 'N Unit'
    | 2.   Penambahan 1 jenis stock baru yakni '1 Unit'
    |
    | o Case 2 Stock Type => 1 Stock Type
    | Solusi : 
    | 1.   Jika stock '1 Unit' belum berubah maka:
    | 1.1. Hapus stock '1 Unit'
    | 1.2. Perubahan jumlah dan informasi serta remarks_note dari 'N Unit' berubah menjadi '-'
    |
    | 2. Jika stock '1 Unit' sudah berubah maka:
    | 2.1. Perubahan jumlah dan informasi 'N Unit'
    | 2.2. Perubahan jumlah dan informasi '1 Unit' dengan nilai yang sama dengan 'N Unit'
    */
    public static function integerRuleUpdateAdd($data)
    {
        $convertedData = self::convertProductsToIntegerRule($data);

        foreach ($convertedData as $groupProduct) {
            $histories = ProductDetailHistoryRepository::getBy(whereClause: [
                ['remarks_id', $groupProduct[0]['remarks_id']],
                ['remarks_type', $groupProduct[0]['remarks_type']],
            ]);

            // New Added
            if (count($histories) == 0) {
                foreach ($groupProduct as $item) {
                    StockHandler::createStock(
                        productId: $item['product_id'],
                        companyId: $item['company_id'],
                        warehouseId: $item['warehouse_id'],
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
            // Case 2 Stock Type => 2 Stock Type
            else if (count($histories) == count($groupProduct)) {
                foreach ($groupProduct as $item) {
                    // Update Information
                    ProductDetailRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $item['remarks_id']],
                            ['remarks_type', $item['remarks_type']],
                            ['remarks_note', $item['remarks_note']],
                        ],
                        data: [
                            'entry_date' => $item['transaction_date'],
                            'expired_date' => $item['expired_date'],
                            'batch' => $item['batch'],
                            'price' => $item['price'],
                            'code' => $item['code'],
                        ]
                    );

                    // Update History
                    ProductDetailHistoryRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $item['remarks_id']],
                            ['remarks_type', $item['remarks_type']],
                            ['remarks_note', $item['remarks_note']],
                        ],
                        data: [
                            'transaction_date' => $item['transaction_date'],
                            'quantity' => $item['quantity'],
                        ]
                    );

                    // Confirm Stock
                    $stock = StockHandler::getStockByRemarks(
                        remarksId: $item['remarks_id'],
                        remarksType: $item['remarks_type'],
                        remarksNote: $item['remarks_note'],
                        transactionSign: 1,
                        isGrouped: true
                    );
                    if ($stock < 0) {
                        throw new \Exception(ErrorMessageHelper::stockNotAvailable($item['product_name']));
                    }
                }
            }
            // Case 1 Stock Type => 2 Stock Type
            else if (count($histories) == 1 && count($groupProduct) == 2) {
                /*
                | ==============================
                | ====== UPDATE 'N Unit' =======
                | ==============================
                */
                // Update Information
                ProductDetailRepository::updateBy(
                    whereClause: [
                        ['remarks_id', $groupProduct[0]['remarks_id']],
                        ['remarks_type', $groupProduct[0]['remarks_type']],
                        ['remarks_note', '-'],
                    ],
                    data: [
                        'entry_date' => $groupProduct[0]['transaction_date'],
                        'expired_date' => $groupProduct[0]['expired_date'],
                        'batch' => $groupProduct[0]['batch'],
                        'price' => $groupProduct[0]['price'],
                        'code' => $groupProduct[0]['code'],
                        'remarks_note' => $groupProduct[0]['remarks_note'],
                    ]
                );

                // Update History
                ProductDetailHistoryRepository::updateBy(
                    whereClause: [
                        ['remarks_id', $groupProduct[0]['remarks_id']],
                        ['remarks_type', $groupProduct[0]['remarks_type']],
                        ['remarks_note', '-'],
                    ],
                    data: [
                        'transaction_date' => $groupProduct[0]['transaction_date'],
                        'quantity' => $groupProduct[0]['quantity'],
                        'remarks_note' => $groupProduct[0]['remarks_note'],
                    ]
                );

                // Confirm Stock
                $stock = StockHandler::getStockByRemarks(
                    remarksId: $groupProduct[0]['remarks_id'],
                    remarksType: $groupProduct[0]['remarks_type'],
                    remarksNote: $groupProduct[0]['remarks_note'],
                    transactionSign: 1,
                    isGrouped: true
                );
                if ($stock < 0) {
                    throw new \Exception(ErrorMessageHelper::stockNotAvailable($groupProduct[0]['product_name']));
                }

                /*
                | ===========================
                | ====== ADD '1 Unit' =======
                | ===========================
                */
                StockHandler::createStock(
                    productId: $groupProduct[1]['product_id'],
                    companyId: $groupProduct[1]['company_id'],
                    warehouseId: $groupProduct[1]['warehouse_id'],
                    transactionDate: $groupProduct[1]['transaction_date'],
                    quantity: $groupProduct[1]['quantity'],
                    price: $groupProduct[1]['price'],
                    code: $groupProduct[1]['code'],
                    batch: $groupProduct[1]['batch'],
                    expiredDate: $groupProduct[1]['expired_date'],
                    remarksId: $groupProduct[1]['remarks_id'],
                    remarksType: $groupProduct[1]['remarks_type'],
                    remarksNote: $groupProduct[1]['remarks_note']
                );
            }
            // Case 2 Stock Type => 1 Stock Type
            else if (count($histories) == 2 && count($groupProduct) == 1) {
                $isStockMoved = StockHandler::isStockMovedByRemarks(
                    remarksId: $groupProduct[0]['remarks_id'],
                    remarksType: $groupProduct[0]['remarks_type'],
                    remarksNote: '1 Unit',
                    transactionSign: 1,
                    isGrouped: true,
                );

                if (!$isStockMoved) {
                    /*
                    | ==============================
                    | ====== CANCEL '1 Unit' =======
                    | ==============================
                    */
                    StockHandler::cancel([
                        [
                            'remarks_id' => $groupProduct[0]['remarks_id'],
                            'remarks_type' => $groupProduct[0]['remarks_type'],
                            'remarks_note' => '1 Unit'
                        ]
                    ]);

                    /*
                    | ==============================
                    | ====== UPDATE 'N Unit' =======
                    | ==============================
                    */
                    // Update Information
                    ProductDetailRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $groupProduct[0]['remarks_id']],
                            ['remarks_type', $groupProduct[0]['remarks_type']],
                            ['remarks_note', 'N Unit'],
                        ],
                        data: [
                            'entry_date' => $groupProduct[0]['transaction_date'],
                            'expired_date' => $groupProduct[0]['expired_date'],
                            'batch' => $groupProduct[0]['batch'],
                            'price' => $groupProduct[0]['price'],
                            'code' => $groupProduct[0]['code'],
                            'remarks_note' => $groupProduct[0]['remarks_note'],
                        ]
                    );

                    // Update History
                    ProductDetailHistoryRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $groupProduct[0]['remarks_id']],
                            ['remarks_type', $groupProduct[0]['remarks_type']],
                            ['remarks_note', 'N Unit'],
                        ],
                        data: [
                            'transaction_date' => $groupProduct[0]['transaction_date'],
                            'quantity' => $groupProduct[0]['quantity'],
                            'remarks_note' => $groupProduct[0]['remarks_note'],
                        ]
                    );

                    // Confirm Stock
                    $stock = StockHandler::getStockByRemarks(
                        remarksId: $groupProduct[0]['remarks_id'],
                        remarksType: $groupProduct[0]['remarks_type'],
                        remarksNote: $groupProduct[0]['remarks_note'],
                        transactionSign: 1,
                        isGrouped: true
                    );
                    if ($stock < 0) {
                        throw new \Exception(ErrorMessageHelper::stockNotAvailable($groupProduct[0]['product_name']));
                    }
                } else {
                    /*
                    | ==============================
                    | ====== UPDATE '1 Unit' =======
                    | ==============================
                    */
                    // Update Information
                    ProductDetailRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $groupProduct[0]['remarks_id']],
                            ['remarks_type', $groupProduct[0]['remarks_type']],
                            ['remarks_note', '1 Unit'],
                        ],
                        data: [
                            'entry_date' => $groupProduct[0]['transaction_date'],
                            'expired_date' => $groupProduct[0]['expired_date'],
                            'batch' => $groupProduct[0]['batch'],
                            'price' => $groupProduct[0]['price'],
                            'code' => $groupProduct[0]['code'],
                            'remarks_note' => '1 Unit',
                        ]
                    );

                    // Update History
                    ProductDetailHistoryRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $groupProduct[0]['remarks_id']],
                            ['remarks_type', $groupProduct[0]['remarks_type']],
                            ['remarks_note', '1 Unit'],
                        ],
                        data: [
                            'transaction_date' => $groupProduct[0]['transaction_date'],
                            'quantity' => 1,
                            'remarks_note' => '1 Unit',
                        ]
                    );

                    // Confirm Stock
                    $stock = StockHandler::getStockByRemarks(
                        remarksId: $groupProduct[0]['remarks_id'],
                        remarksType: $groupProduct[0]['remarks_type'],
                        remarksNote: '1 Unit',
                        transactionSign: 1,
                        isGrouped: true
                    );
                    if ($stock < 0) {
                        throw new \Exception(ErrorMessageHelper::stockNotAvailable($groupProduct[0]['product_name']));
                    }

                    /*
                    | ==============================
                    | ====== UPDATE 'N Unit' =======
                    | ==============================
                    */

                    // Update Information
                    ProductDetailRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $groupProduct[0]['remarks_id']],
                            ['remarks_type', $groupProduct[0]['remarks_type']],
                            ['remarks_note', 'N Unit'],
                        ],
                        data: [
                            'entry_date' => $groupProduct[0]['transaction_date'],
                            'expired_date' => $groupProduct[0]['expired_date'],
                            'batch' => $groupProduct[0]['batch'],
                            'price' => $groupProduct[0]['price'],
                            'code' => $groupProduct[0]['code'],
                            'remarks_note' => 'N Unit',
                        ]
                    );

                    // Update History
                    ProductDetailHistoryRepository::updateBy(
                        whereClause: [
                            ['remarks_id', $groupProduct[0]['remarks_id']],
                            ['remarks_type', $groupProduct[0]['remarks_type']],
                            ['remarks_note', 'N Unit'],
                        ],
                        data: [
                            'transaction_date' => $groupProduct[0]['transaction_date'],
                            'quantity' => $groupProduct[0]['quantity'] - 1,
                            'remarks_note' => 'N Unit',
                        ]
                    );

                    // Confirm Stock
                    $stock = StockHandler::getStockByRemarks(
                        remarksId: $groupProduct[0]['remarks_id'],
                        remarksType: $groupProduct[0]['remarks_type'],
                        remarksNote: 'N Unit',
                        transactionSign: 1,
                        isGrouped: true
                    );
                    if ($stock < 0) {
                        throw new \Exception(ErrorMessageHelper::stockNotAvailable($groupProduct[0]['product_name']));
                    }
                }
            }
        }
    }
}
