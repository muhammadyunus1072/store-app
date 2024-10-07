<?php

namespace Tests\Feature\Logistic\Stock;

use App\Helpers\Logistic\StockHelper;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Prompts\Output\ConsoleOutput;
use Tests\TestCase;

class UpdateAddStockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */

    protected $seed = true;

    public function test_product_stock(): void
    {
        $consoleOuput = new ConsoleOutput();

        $company = CompanyRepository::find(1);
        $warehouse = WarehouseRepository::find(1);
        $transactionDate = Carbon::now()->format('Y-m-d');
        $products = ProductRepository::all();

        foreach ($products as $product) {
            $unitDetails = $product->unit->unitDetails;
            $mainUnitDetail = $product->unit->unitDetailMain;

            /*
            | ADD STOCK
            */
            $unitDetail = $unitDetails[max(0, count($unitDetails) - 1)];
            $quantity = 10;
            $price = 10000;
            $expiredDate = '2024-09-12';
            $code = 'XU64283X';
            $batch = 'GJDAO231';
            $remarksId = 0;
            $remarksType = "Test";

            StockHelper::addStock(
                productId: $product->id,
                companyId: $company->id,
                warehouseId: $warehouse->id,
                quantity: $quantity,
                unitDetailId: $unitDetail->id,
                transactionDate: $transactionDate,
                price: $price,
                code: $code,
                batch: $batch,
                expiredDate: $expiredDate,
                remarksId: $remarksId,
                remarksType: $remarksType
            );

            /*
            | UPDATE ADD STOCK
            */
            $newUnitDetail = $unitDetails[0];
            $newQuantity = 20;
            $newPrice = 100;
            $newExpiredDate = '2024-10-12';
            $newCode = 'AAAAAA';
            $newBatch = 'XXXXXX';

            StockHelper::updateAddStock(
                remarksId: $remarksId,
                remarksType: $remarksType,

                productId: $product->id,
                companyId: $company->id,
                warehouseId: $warehouse->id,
                quantity: $newQuantity,
                unitDetailId: $newUnitDetail->id,
                transactionDate: $transactionDate,
                price: $newPrice,
                code: $newCode,
                batch: $newBatch,
                expiredDate: $newExpiredDate,
            );

            /* 
            | ASSERT NEW STOCK
            */
            $newResultConvert = StockHelper::convertUnitPrice($newQuantity, $newPrice, $newUnitDetail->id);
            $newConvertedQuantity = $newResultConvert['quantity'];
            $newConvertedPrice = $newResultConvert['price'];

            $this->assertDatabaseHas('product_stocks', [
                'product_id' => $product->id,
                'quantity' => $newConvertedQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_warehouses', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $newConvertedQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_companies', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'quantity' => $newConvertedQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_company_warehouses', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $newConvertedQuantity,
            ]);

            $this->assertDatabaseHas('product_details', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'entry_date' => $transactionDate,
                'price' => $newConvertedPrice,
                'expired_date' => $newExpiredDate,
                'batch' => $newBatch,
                'code' => $newCode,
            ]);

            $productDetail = ProductDetailRepository::findBy([
                ['product_id', $product->id],
                ['company_id', $company->id],
                ['warehouse_id', $warehouse->id],
                ['entry_date', $transactionDate],
                ['price', $newConvertedPrice],
                ['expired_date', $newExpiredDate],
                ['batch', $newBatch],
                ['code', $newCode],
            ]);

            $this->assertDatabaseHas('product_stock_details', [
                'product_detail_id' => $productDetail->id,
                'quantity' => $newConvertedQuantity,
            ]);

            /* 
            | ASSERT OLD STOCK
            */
            $resultConvert = StockHelper::convertUnitPrice($quantity, $price, $unitDetail->id);
            $convertedPrice = $resultConvert['price'];

            $this->assertDatabaseHas('product_details', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'entry_date' => $transactionDate,
                'price' => $convertedPrice,
                'expired_date' => $expiredDate,
                'batch' => $batch,
                'code' => $code,
            ]);

            $productDetail = ProductDetailRepository::findBy([
                ['product_id', $product->id],
                ['company_id', $company->id],
                ['warehouse_id', $warehouse->id],
                ['entry_date', $transactionDate],
                ['price', $convertedPrice],
                ['expired_date', $expiredDate],
                ['batch', $batch],
                ['code', $code],
            ]);

            $this->assertDatabaseHas('product_stock_details', [
                'product_detail_id' => $productDetail->id,
                'quantity' => 0,
            ]);
        }
    }
}
