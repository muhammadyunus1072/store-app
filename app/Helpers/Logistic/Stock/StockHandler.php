<?php

namespace App\Helpers\Logistic\Stock;

use App\Helpers\General\ErrorMessageHelper;
use Carbon\Carbon;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;
use App\Repositories\Logistic\Transaction\ProductStock\ProductStockRepository;
use App\Repositories\Logistic\Transaction\ProductStock\ProductStockDetailRepository;
use App\Repositories\Logistic\Transaction\ProductStock\ProductStockWarehouseRepository;
use App\Repositories\Logistic\Transaction\ProductStock\ProductStockCompanyRepository;
use App\Repositories\Logistic\Transaction\ProductStock\ProductStockCompanyWarehouseRepository;
use App\Settings\SettingLogistic;
use Laravel\Prompts\Output\ConsoleOutput;

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

        $isPriceIntegerValue = SettingLogistic::get(SettingLogistic::PRICE_INTEGER_VALUE);

        if ($isPriceIntegerValue) {
            self::integerRuleAdd($data);
        } else {
            self::standardRuleAdd($data);
        }
    }

    public static function updateAdd($data)
    {
        if (count($data) == 0) {
            return;
        }

        $isPriceIntegerValue = SettingLogistic::get(SettingLogistic::PRICE_INTEGER_VALUE);

        if ($isPriceIntegerValue) {
            self::integerRuleUpdateAdd($data);
        } else {
            self::standardRuleUpdateAdd($data);
        }
    }

    public static function substract($data)
    {
        if (count($data) == 0) {
            return;
        }

        $createdHistories = [];

        foreach ($data as $item) {
            $stock = self::getStockCompanyWarehouse($item['product_id'], $item['company_id'], $item['warehouse_id']);
            $resultConvert = self::convertUnitPrice($item['quantity'], 0, $item['unit_detail_id']);
            $substractQty = $resultConvert['quantity'];

            // Check Availability Stock
            if (empty($stock) || $substractQty > $stock->quantity) {
                throw new \Exception(ErrorMessageHelper::stockNotAvailable($item['product_name'], $resultConvert['unit_detail_name'], $stock->quantity, $item['quantity']));
            }

            // Get Substract Stock Method
            $substractStockMethod = SettingLogistic::get(SettingLogistic::SUBSTRACT_STOCK_METHOD);

            // Substract Stock Process
            $productDetails = ProductDetailRepository::getBySubstractMethod(
                productId: $item['product_id'],
                companyId: $item['company_id'],
                warehouseId: $item['warehouse_id'],
                substractStockMethod: $substractStockMethod
            );

            foreach ($productDetails as $productDetail) {
                $usedQty = min($productDetail->productStockDetail->quantity, $substractQty) * -1;

                $createdHistories[$item['id']][] = ProductDetailHistoryRepository::create([
                    'product_detail_id' => $productDetail->id,
                    'transaction_date' => Carbon::now(),
                    'quantity' => $usedQty,
                    'remarks_id' => $item['remarks_id'],
                    'remarks_type' => $item['remarks_type'],
                ]);

                $substractQty += $usedQty;

                if ($substractQty == 0) {
                    break;
                }
            }

            if ($substractQty) {
                throw new \Exception(ErrorMessageHelper::stockNotAvailable($item['product_name'], $resultConvert['unit_detail_name'], $stock->quantity, $item['quantity']));
            }
        }

        return $createdHistories;
    }

    public static function updateSubstract($data)
    {
        if (count($data) == 0) {
            return;
        }

        foreach ($data as $item) {
            $resultConvert = self::convertUnitPrice($item['quantity'], 0, $item['unit_detail_id']);
            $histories = ProductDetailHistoryRepository::getBy(
                whereClause: [
                    ['remarks_id', $item['remarks_id']],
                    ['remarks_type', $item['remarks_type']],
                ],
                orderByClause: [
                    ['id', 'DESC']
                ]
            );

            $quantityBefore = abs($histories->sum('quantity'));
            $quantityAfter = $resultConvert['quantity'];
            $quantityDiff = $quantityAfter - $quantityBefore;

            if ($quantityDiff > 0) {
                $item['quantity'] = $quantityDiff;
                self::substract([$item]);
            } else if ($quantityDiff < 0) {
                foreach ($histories as $history) {
                    if ($quantityDiff < $history->quantity) {
                        ProductDetailRepository::delete($history->id);
                        $quantityDiff -= $history->quantity;
                    } else {
                        ProductDetailHistoryRepository::update($history->id, [
                            'quantity' => $history->quantity - $quantityDiff,
                        ]);
                        $quantityDiff = 0;
                    }

                    if ($quantityDiff == 0) {
                        break;
                    }
                }

                if ($quantityDiff != 0) {
                    throw new \Exception(ErrorMessageHelper::stockNotAvailable($item['product_name']));
                }
            }
        }
    }

    public static function transfer($data)
    {
        if (count($data) == 0) {
            return;
        }

        foreach ($data as $item) {
            $item['company_id'] = $item['company_requested_id'];
            $item['warehouse_id'] = $item['warehouse_requested_id'];
        }

        $createdHistories = self::substract($data);

        foreach ($data as $item) {
            foreach ($createdHistories[$item['id']] as $history) {
                self::createStock(
                    productId: $item['product_id'],
                    companyId: $item['company_requester_id'],
                    warehouseId: $item['warehouse_requester_id'],
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

    public static function updateTransfer($data)
    {
        if (count($data) == 0) {
            return;
        }

        foreach ($data as $item) {
            $resultConvert = self::convertUnitPrice($item['quantity'], 0, $item['unit_detail_id']);
            $substractHistories = ProductDetailHistoryRepository::getBy(
                whereClause: [
                    ['remarks_id', $item['remarks_id']],
                    ['remarks_type', $item['remarks_type']],
                    ['quantity', '<', 0],
                ],
                orderByClause: [
                    ['id', 'DESC']
                ]
            );

            $quantityBefore = abs($substractHistories->sum('quantity'));
            $quantityAfter = $resultConvert['quantity'];
            $quantityDiff = $quantityAfter - $quantityBefore;

            if ($quantityDiff > 0) {
                $item['quantity'] = $quantityDiff;
                self::transfer([$item]);
            } else if ($quantityDiff < 0) {
                foreach ($substractHistories as $substractHistory) {
                    // Get Add History in Warehouse Requester
                    $addHistories = ProductDetailHistoryRepository::getBy(
                        whereClause: [
                            ['remarks_id', $item['remarks_id']],
                            ['remarks_type', $item['remarks_type']],
                            ['remarks_note', $substractHistory->id],
                            ['quantity', '>', 0],
                        ]
                    );

                    foreach ($addHistories as $addHistory) {
                        // Check Available Stock
                        $availableStock = $addHistory->productDetail->productStockDetail->quantity;
                        if ($availableStock <= 0) {
                            continue;
                        }

                        if (abs($quantityDiff) > $availableStock) {
                            if ($availableStock == $addHistory->quantity) {
                                ProductDetailHistoryRepository::delete($addHistory->id);
                                ProductDetailHistoryRepository::delete($substractHistory->id);
                            } else {
                                ProductDetailHistoryRepository::update($addHistory->id, [
                                    'quantity' => $addHistory->quantity - $availableStock,
                                ]);
                                ProductDetailHistoryRepository::update($substractHistory->id, [
                                    'quantity' => $substractHistory->quantity + $availableStock,
                                ]);
                            }

                            $quantityDiff += $availableStock;
                        } else {
                            ProductDetailHistoryRepository::update($addHistory->id, [
                                'quantity' => $addHistory->quantity - $availableStock,
                            ]);
                            ProductDetailHistoryRepository::update($substractHistory->id, [
                                'quantity' => $substractHistory->quantity + $availableStock,
                            ]);
                            $quantityDiff = 0;
                        }
                    }

                    if ($quantityDiff == 0) {
                        break;
                    }
                }

                if ($quantityDiff != 0) {
                    throw new \Exception(ErrorMessageHelper::stockNotAvailable($item['product_name']));
                }
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
            $histories = ProductDetailHistoryRepository::getBy($whereClause);
            $affectedProductDetails = [];
            foreach ($histories as $history) {
                $affectedProductDetails[] = $history->productDetail;
                $history->delete();
            }
            
            // Check Histories & Current Stock
            foreach ($affectedProductDetails as $productDetail) {
                if ($productDetail->histories()->count() == 0) {
                    $productDetail->delete();
                } else if (StockHandler::getStockDetail($productDetail->id) < 0) {
                    throw new \Exception(ErrorMessageHelper::stockNotAvailable($productDetail->product->name));
                }
            }
            
        }
    }

    /*
    | CALCULATE CURRENT STOCK
    */
    public static function calculateStock($productId = null)
    {
        if (app()->runningInConsole()) {
            $consoleOutput = new ConsoleOutput();
        }

        $dataSumStock = ProductDetailHistoryRepository::getSumStock(
            productDetailId: null,
            productId: $productId,
            warehouseId: null,
            companyId: null,
            groupByProductDetailId: false,
            groupByProductId: true,
            groupByCompanyId: false,
            groupByWarehouseId: false,
        );

        foreach ($dataSumStock as $index => $itemSumStock) {
            ProductStockRepository::createOrUpdate(
                $itemSumStock->product_id,
                $itemSumStock->sum_quantity
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: " . ($index + 1) . " / " . count($dataSumStock));
        }

        // CASE: PRODUCT DETAIL HISTORIES EMPTY
        if (count($dataSumStock) > 0) {
            return;
        }

        !isset($consoleOutput) ?: $consoleOutput->writeln("CALCULATE STOCK CASE: PRODUCT DETAIL HISTORIES EMPTY");

        if ($productId != null) {
            ProductStockRepository::updateBy(
                whereClause: [['product_id', $productId]],
                data: ['quantity' => 0],
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: ONLY PRODUCT ID = $productId");
        } else {
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            foreach ($products as $index => $product) {
                ProductStockRepository::updateBy(
                    whereClause: [['product_id', $product->id]],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: " . ($index + 1) . " / " . count($products));
            }
        }
    }

    public static function calculateStockDetail($productDetailId = null)
    {
        if (app()->runningInConsole()) {
            $consoleOutput = new ConsoleOutput();
        }

        $dataSumStock = ProductDetailHistoryRepository::getSumStock(
            productDetailId: $productDetailId,
            productId: null,
            companyId: null,
            warehouseId: null,
            groupByProductDetailId: true,
            groupByProductId: false,
            groupByCompanyId: false,
            groupByWarehouseId: false,
        );

        foreach ($dataSumStock as $index => $itemSumStock) {
            ProductStockDetailRepository::createOrUpdate(
                $itemSumStock->product_detail_id,
                $itemSumStock->sum_quantity
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: " . ($index + 1) . " / " . count($dataSumStock));
        }

        // CASE: PRODUCT DETAIL HISTORIES EMPTY
        if (count($dataSumStock) > 0) {
            return;
        }

        !isset($consoleOutput) ?: $consoleOutput->writeln("CALCULATE STOCK DETAIL CASE: PRODUCT DETAIL HISTORIES EMPTY");

        if ($productDetailId != null) {
            ProductStockDetailRepository::updateBy(
                whereClause: [['product_detail_id', $productDetailId]],
                data: ['quantity' => 0],
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: ONLY PRODUCT DETAIL ID = $productDetailId");
        } else {
            $productDetails = ProductDetailRepository::all();
            foreach ($productDetails as $index => $productDetail) {
                ProductStockDetailRepository::updateBy(
                    whereClause: [['product_detail_id', $productDetail->id]],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: " . ($index + 1) . " / " . count($productDetails));
            }
        }
    }

    public static function calculateStockWarehouse($productId = null, $warehouseId = null)
    {
        if (app()->runningInConsole()) {
            $consoleOutput = new ConsoleOutput();
        }

        $dataSumStock = ProductDetailHistoryRepository::getSumStock(
            productDetailId: null,
            productId: $productId,
            companyId: null,
            warehouseId: $warehouseId,
            groupByProductDetailId: false,
            groupByProductId: true,
            groupByCompanyId: false,
            groupByWarehouseId: true,
        );

        foreach ($dataSumStock as $index => $itemSumStock) {
            ProductStockWarehouseRepository::createOrUpdate(
                $itemSumStock->product_id,
                $itemSumStock->warehouse_id,
                $itemSumStock->sum_quantity
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: " . ($index + 1) . " / " . count($dataSumStock));
        }

        // CASE: PRODUCT DETAIL HISTORIES EMPTY
        if (count($dataSumStock) > 0) {
            return;
        }

        !isset($consoleOutput) ?: $consoleOutput->writeln("CALCULATE STOCK WAREHOUSE CASE: PRODUCT DETAIL HISTORIES EMPTY");

        if ($productId != null && $warehouseId != null) {
            ProductStockWarehouseRepository::updateBy(
                whereClause: [
                    ['product_id', $productId],
                    ['warehouse_id', $warehouseId]
                ],
                data: ['quantity' => 0],
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: ONLY PRODUCT ID = $productId AND WAREHOUSE ID = $warehouseId");
        } else if ($productId != null) {
            $warehouses = WarehouseRepository::all();
            foreach ($warehouses as $index => $warehouse) {
                ProductStockWarehouseRepository::updateBy(
                    whereClause: [
                        ['product_id', $productId],
                        ['warehouse_id', $warehouse->id]
                    ],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL WAREHOUSE): " . ($index + 1) . " / " . count($warehouses));
            }
        } else if ($warehouseId != null) {
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            foreach ($products as $index => $product) {
                ProductStockWarehouseRepository::updateBy(
                    whereClause: [
                        ['product_id', $product->id],
                        ['warehouse_id', $warehouseId]
                    ],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCTS): " . ($index + 1) . " / " . count($products));
            }
        } else {
            $warehouses = WarehouseRepository::all();
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            $iteration = 1;
            $maxIteration = count($products) * count($warehouses);

            foreach ($products as $product) {
                foreach ($warehouses as $warehouse) {
                    ProductStockWarehouseRepository::updateBy(
                        whereClause: [
                            ['product_id', $product->id],
                            ['warehouse_id', $warehouse->id]
                        ],
                        data: ['quantity' => 0],
                    );

                    !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCT & WAREHOUSE): " . ($iteration++) . " / $maxIteration");
                }
            }
        }
    }

    public static function calculateStockCompany($productId = null, $companyId = null)
    {
        if (app()->runningInConsole()) {
            $consoleOutput = new ConsoleOutput();
        }

        $dataSumStock = ProductDetailHistoryRepository::getSumStock(
            productDetailId: null,
            productId: $productId,
            companyId: $companyId,
            warehouseId: null,
            groupByProductDetailId: false,
            groupByProductId: true,
            groupByCompanyId: true,
            groupByWarehouseId: false,
        );

        foreach ($dataSumStock as $index => $itemSumStock) {
            ProductStockCompanyRepository::createOrUpdate(
                $itemSumStock->product_id,
                $itemSumStock->company_id,
                $itemSumStock->sum_quantity
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: " . ($index + 1) . " / " . count($dataSumStock));
        }

        // CASE: PRODUCT DETAIL HISTORIES EMPTY
        if (count($dataSumStock) > 0) {
            return;
        }

        !isset($consoleOutput) ?: $consoleOutput->writeln("CALCULATE STOCK COMPANY CASE: PRODUCT DETAIL HISTORIES EMPTY");

        if ($productId != null && $companyId != null) {
            ProductStockCompanyRepository::updateBy(
                whereClause: [
                    ['product_id', $productId],
                    ['company_id', $companyId]
                ],
                data: ['quantity' => 0],
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: ONLY PRODUCT ID = $productId AND COPMANY ID = $companyId");
        } else if ($productId != null) {
            $companies = CompanyRepository::all();
            foreach ($companies as $index => $company) {
                ProductStockCompanyRepository::updateBy(
                    whereClause: [
                        ['product_id', $productId],
                        ['company_id', $company->id]
                    ],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL COMPANY): " . ($index + 1) . " / " . count($companies));
            }
        } else if ($companyId != null) {
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            foreach ($products as $index => $product) {
                ProductStockCompanyRepository::updateBy(
                    whereClause: [
                        ['product_id', $product->id],
                        ['company_id', $companyId]
                    ],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCTS): " . ($index + 1) . " / " . count($products));
            }
        } else {
            $companies = CompanyRepository::all();
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            $iteration = 1;
            $maxIteration = count($products) * count($companies);

            foreach ($products as $product) {
                foreach ($companies as $company) {
                    ProductStockCompanyRepository::updateBy(
                        whereClause: [
                            ['product_id', $product->id],
                            ['company_id', $company->id]
                        ],
                        data: ['quantity' => 0],
                    );

                    !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCT & COMPANY): " . ($iteration++) . " / $maxIteration");
                }
            }
        }
    }

    public static function calculateStockCompanyWarehouse($productId = null, $companyId = null, $warehouseId = null)
    {
        if (app()->runningInConsole()) {
            $consoleOutput = new ConsoleOutput();
        }

        $dataSumStock = ProductDetailHistoryRepository::getSumStock(
            productDetailId: null,
            productId: $productId,
            companyId: $companyId,
            warehouseId: $warehouseId,
            groupByProductDetailId: false,
            groupByProductId: true,
            groupByCompanyId: true,
            groupByWarehouseId: true,
        );

        foreach ($dataSumStock as $index => $itemSumStock) {
            ProductStockCompanyWarehouseRepository::createOrUpdate(
                $itemSumStock->product_id,
                $itemSumStock->company_id,
                $itemSumStock->warehouse_id,
                $itemSumStock->sum_quantity
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: " . ($index + 1) . " / " . count($dataSumStock));
        }

        // CASE: PRODUCT DETAIL HISTORIES EMPTY
        if (count($dataSumStock) > 0) {
            return;
        }

        !isset($consoleOutput) ?: $consoleOutput->writeln("CALCULATE STOCK COMPANY WAREHOUSE CASE: PRODUCT DETAIL HISTORIES EMPTY");

        if ($productId != null && $companyId != null && $warehouseId != null) {
            ProductStockCompanyWarehouseRepository::updateBy(
                whereClause: [
                    ['product_id', $productId],
                    ['company_id', $companyId],
                    ['warehouse_id', $warehouseId]
                ],
                data: ['quantity' => 0],
            );

            !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS: ONLY PRODUCT ID = $productId AND WAREHOUSE ID = $warehouseId");
        } else if ($companyId != null && $warehouseId != null) {
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            foreach ($products as $index => $product) {
                ProductStockCompanyWarehouseRepository::updateBy(
                    whereClause: [
                        ['product_id', $product->id],
                        ['company_id', $companyId],
                        ['warehouse_id', $warehouseId]
                    ],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCTS): " . ($index + 1) . " / " . count($products));
            }
        } else if ($productId != null && $companyId != null) {
            $warehouses = WarehouseRepository::all();
            foreach ($warehouses as $index => $warehouse) {
                ProductStockCompanyWarehouseRepository::updateBy(
                    whereClause: [
                        ['product_id', $productId],
                        ['company_id', $companyId],
                        ['warehouse_id', $warehouse->id]
                    ],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCTS): " . ($index + 1) . " / " . count($warehouses));
            }
        } else if ($productId != null && $warehouseId != null) {
            $companies = CompanyRepository::all();
            foreach ($companies as $index => $company) {
                ProductStockCompanyWarehouseRepository::updateBy(
                    whereClause: [
                        ['product_id', $productId],
                        ['company_id', $company->id],
                        ['warehouse_id', $warehouseId]
                    ],
                    data: ['quantity' => 0],
                );

                !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCTS): " . ($index + 1) . " / " . count($companies));
            }
        } else if ($warehouseId != null) {
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            $companies = CompanyRepository::all();
            $iteration = 1;
            $maxIteration = count($products) * count($companies);

            foreach ($products as $index => $product) {
                foreach ($companies as $index => $company) {
                    ProductStockCompanyWarehouseRepository::updateBy(
                        whereClause: [
                            ['product_id', $product->id],
                            ['company_id', $company->id],
                            ['warehouse_id', $warehouseId]
                        ],
                        data: ['quantity' => 0],
                    );

                    !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCT & COMPANY): " . ($iteration++) . " / $maxIteration");
                }
            }
        } else if ($companyId != null) {
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            $warehouses = WarehouseRepository::all();
            $iteration = 1;
            $maxIteration = count($products) * count($warehouses);

            foreach ($products as $index => $product) {
                foreach ($warehouses as $index => $warehouse) {
                    ProductStockCompanyWarehouseRepository::updateBy(
                        whereClause: [
                            ['product_id', $product->id],
                            ['company_id', $companyId],
                            ['warehouse_id', $warehouse->id]
                        ],
                        data: ['quantity' => 0],
                    );

                    !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCT & WAREHOUSE): " . ($index + 1) . " / $maxIteration");
                }
            }
        } else if ($productId != null) {
            $companies = CompanyRepository::all();
            $warehouses = WarehouseRepository::all();
            $iteration = 1;
            $maxIteration = count($companies) * count($warehouses);
            foreach ($companies as $index => $company) {
                foreach ($warehouses as $index => $warehouse) {
                    ProductStockCompanyWarehouseRepository::updateBy(
                        whereClause: [
                            ['product_id', $productId],
                            ['company_id', $company->id],
                            ['warehouse_id', $warehouse->id]
                        ],
                        data: ['quantity' => 0],
                    );

                    !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL COMPANY & WAREHOUSE): " . ($index + 1) . " / $maxIteration");
                }
            }
        } else {
            $companies = CompanyRepository::all();
            $warehouses = WarehouseRepository::all();
            $products = ProductRepository::getBy(whereClause: ['type' => Product::TYPE_PRODUCT_WITH_STOCK]);
            $iteration = 1;
            $maxIteration = count($products) * count($companies) * count($warehouses);

            foreach ($products as $product) {
                foreach ($companies as $company) {
                    foreach ($warehouses as $warehouse) {
                        ProductStockCompanyWarehouseRepository::updateBy(
                            whereClause: [
                                ['product_id', $product->id],
                                ['company_id', $company->id],
                                ['warehouse_id', $warehouse->id]
                            ],
                            data: ['quantity' => 0]
                        );

                        !isset($consoleOutput) ?: $consoleOutput->writeln("PROGRESS (ALL PRODUCT & COMPANY & WAREHOUSE): " . ($iteration++) . " / $maxIteration");
                    }
                }
            }
        }
    }

    /*
    | GET CURRENT STOCK
    */
    public static function getStock($productId)
    {
        return ProductStockRepository::findBy(
            whereClause: [
                ['product_id', $productId]
            ]
        )->quantity;
    }

    public static function getStockDetail($productDetailId)
    {
        return ProductStockDetailRepository::findBy(
            whereClause: [
                ['product_detail_id', $productDetailId]
            ]
        )->quantity;
    }

    public static function getStockWarehouse($productId, $warehouseId)
    {
        return ProductStockWarehouseRepository::findBy(
            whereClause: [
                ['product_id', $productId],
                ['warehouse_id', $warehouseId]
            ]
        )->quantity;
    }

    public static function getStockCompany($productId, $companyId)
    {
        return ProductStockCompanyRepository::findBy(
            whereClause: [
                ['product_id', $productId],
                ['company_id', $companyId]
            ]
        )->quantity;
    }

    public static function getStockCompanyWarehouse($productId, $companyId, $warehouseId)
    {
        return ProductStockCompanyWarehouseRepository::findBy(
            whereClause: [
                ['product_id', $productId],
                ['company_id', $companyId],
                ['warehouse_id', $warehouseId]
            ]
        )->quantity;
    }

    public static function getStockHistories(
        $remarksId,
        $remarksType,
        $remarksNote = null,
        $transactionSign = null,
    ) {
        // Where Clause
        $whereClause =  [
            ['remarks_id', $remarksId],
            ['remarks_type', $remarksType],
        ];

        if ($remarksNote != null) {
            $whereClause[] = ['remarks_note', $remarksNote];
        }

        if ($transactionSign != null) {
            $whereClause[] = ['quantity', ($transactionSign ? '>' : '<'), 0];
        }

        // Order By Clause
        $orderByClause = [['id', 'DESC']];

        return ProductDetailHistoryRepository::getBy($whereClause, $orderByClause);
    }

    public static function getStockByRemarks(
        $remarksId,
        $remarksType,
        $remarksNote,
        $transactionSign = null,
        $isGrouped = false,
    ) {
        $histories = self::getStockHistories($remarksId, $remarksType, $remarksNote, $transactionSign);

        // Check Every Affected Product Details
        $data = [];
        foreach ($histories as $history) {
            if (!isset($history->product_detail_id)) {
                $data[$history->product_detail_id] = [
                    'product_detail_id' => $history->product_detail_id,
                    'stock' => self::getStockDetail($history->product_detail_id),
                ];
            }
        }

        // Return Value
        if ($isGrouped) {
            $total = 0;
            foreach ($data as $item) {
                $total += $item['stock'];
            }
            return $total;
        } else {
            return $data;
        }
    }

    public static function isStockMovedByRemarks(
        $remarksId,
        $remarksType,
        $remarksNote,
        $transactionSign = null,
        $isGrouped = false,
    ) {
        $histories = self::getStockHistories($remarksId, $remarksType, $remarksNote, $transactionSign);

        // Check Every Affected Product Details
        $data = [];
        foreach ($histories as $history) {
            if (!isset($data[$history->product_detail_id])) {
                $data[$history->product_detail_id] = [
                    'product_detail_id' => $history->product_detail_id,
                    'is_stock_moved' => ProductDetailHistoryRepository::getNewerHistories($history)->count() > 0,
                ];
            }
        }

        // Return Value
        if ($isGrouped) {
            foreach ($data as $item) {
                if ($item['is_stock_moved']) {
                    return true;
                }
            }
            return false;
        } else {
            return $data;
        }
    }
}
