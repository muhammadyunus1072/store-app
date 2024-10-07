<?php

namespace App\Helpers\Logistic;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Core\Setting\Setting;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Transaction\ProductStock\ProductStockRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;
use App\Repositories\Logistic\Transaction\ProductStockDetail\ProductStockDetailRepository;
use App\Repositories\Logistic\Transaction\ProductDetailHistory\ProductDetailHistoryRepository;
use App\Repositories\Logistic\Transaction\ProductStockWarehouse\ProductStockWarehouseRepository;
use App\Repositories\Logistic\Transaction\ProductStockCompany\ProductStockCompanyRepository;
use App\Repositories\Logistic\Transaction\ProductStockCompanyWarehouse\ProductStockCompanyWarehouseRepository;
use Laravel\Prompts\Output\ConsoleOutput;

class StockHelper
{

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
        $fromUnitDetailId,
        $targetUnitDetailId = null,
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
        ];
    }

    /*
    | TRANSACTION STOCK
    */
    public static function addStock(
        $productId,
        $companyId,
        $warehouseId,
        $quantity,
        $unitDetailId,
        $transactionDate,
        $price,
        $code = null,
        $batch = null,
        $expiredDate = null,
        $remarksId = null,
        $remarksType = null,
    ) {
        $resultConvert = self::convertUnitPrice($quantity, $price, $unitDetailId);

        $productDetail = ProductDetailRepository::createIfNotExist(
            productId: $productId,
            companyId: $companyId,
            warehouseId: $warehouseId,
            entryDate: $transactionDate,
            price: $resultConvert['price'],
            code: $code,
            batch: $batch,
            expiredDate: $expiredDate
        );

        ProductDetailHistoryRepository::create([
            'product_detail_id' => $productDetail->id,
            'transaction_date' => $transactionDate,
            'quantity' => $resultConvert['quantity'],
            'remarks_id' => $remarksId,
            'remarks_type' => $remarksType,
        ]);
    }

    public static function updateAddStock(
        $remarksId,
        $remarksType,
        $productId,
        $companyId,
        $warehouseId,
        $quantity,
        $unitDetailId,
        $transactionDate,
        $price,
        $code = null,
        $batch = null,
        $expiredDate = null,
    ) {
        ProductDetailHistoryRepository::deleteBy(
            whereClause: [
                ['remarks_id', $remarksId],
                ['remarks_type', $remarksType],
            ]
        );

        // Re Adding Stock
        self::addStock(
            $productId,
            $companyId,
            $warehouseId,
            $quantity,
            $unitDetailId,
            $transactionDate,
            $price,
            $code,
            $batch,
            $expiredDate,
        );
    }

    public static function substractStock(
        $productId,
        $companyId,
        $warehouseId,
        $quantity,
        $unitDetailId,
        $remarksId = null,
        $remarksType = null,
    ) {
        try {
            DB::beginTransaction();

            $stock = self::getStockCompanyWarehouse($productId, $companyId, $warehouseId);
            $resultConvert = self::convertUnitPrice($quantity, 0, $unitDetailId);
            $substractQty = $resultConvert['quantity'];

            // Check Availability Stock
            if (empty($stock) || $substractQty > $stock->quantity) {
                throw new \Exception('Stock Tidak Mencukupi');
            }

            // Get Substract Stock Method
            $setting = SettingRepository::findByName(Setting::NAME_LOGISTIC);
            $settings = json_decode($setting->setting);
            $substractStockMethod = $settings->product_substract_stock_method;

            // Substract Stock Process
            $productDetails = ProductDetailRepository::getBySubstractMethod(
                productId: $productId,
                companyId: $companyId,
                warehouseId: $warehouseId,
                substractStockMethod: $substractStockMethod
            );

            foreach ($productDetails as $productDetail) {
                $usedQty = min($productDetail->productStockDetail->quantity, $substractQty) * -1;

                ProductDetailHistoryRepository::create([
                    'product_detail_id' => $productDetail->id,
                    'transaction_date' => Carbon::now(),
                    'quantity' => $usedQty,
                    'remarks_id' => $remarksId,
                    'remarks_type' => $remarksType,
                ]);

                $substractQty += $usedQty;

                if ($substractQty == 0) {
                    break;
                }
            }

            if ($substractQty) {
                DB::rollBack();
                throw new \Exception('Stock Tidak Mencukupi');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function updateSubstractStock(
        $remarksId,
        $remarksType,
        $productId,
        $companyId,
        $warehouseId,
        $quantity,
        $unitDetailId
    ) {
        ProductDetailHistoryRepository::deleteBy(
            whereClause: [
                ['remarks_id', $remarksId],
                ['remarks_type', $remarksType],
            ]
        );

        self::substractStock(
            $productId,
            $companyId,
            $warehouseId,
            $quantity,
            $unitDetailId
        );
    }

    public static function isTransactionStockUpdatable(
        $remarksId,
        $remarksType
    ) {
        $histories = ProductDetailHistoryRepository::getBy(
            whereClause: [
                ['remarks_id', $remarksId],
                ['remarks_type', $remarksType],
            ]
        );

        foreach ($histories as $history) {
            $countNewerHistories = ProductDetailHistoryRepository::getNewerHistories($history)->count();
            if ($countNewerHistories > 0) {
                return false;
            }
        }

        return true;
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
        );
    }

    public static function getStockDetail($productDetailId)
    {
        return ProductStockDetailRepository::findBy(
            whereClause: [
                ['product_detail_id', $productDetailId]
            ]
        );
    }

    public static function getStockWarehouse($productId, $warehouseId)
    {
        return ProductStockWarehouseRepository::findBy(
            whereClause: [
                ['product_id', $productId],
                ['warehouse_id', $warehouseId]
            ]
        );
    }

    public static function getStockCompany($productId, $companyId)
    {
        return ProductStockCompanyRepository::findBy(
            whereClause: [
                ['product_id', $productId],
                ['company_id', $companyId]
            ]
        );
    }

    public static function getStockCompanyWarehouse($productId, $companyId, $warehouseId)
    {
        return ProductStockCompanyWarehouseRepository::findBy(
            whereClause: [
                ['product_id', $productId],
                ['company_id', $companyId],
                ['warehouse_id', $warehouseId]
            ]
        );
    }
}
