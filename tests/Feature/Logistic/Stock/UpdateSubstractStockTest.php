<?php

namespace Tests\Feature\Logistic\Stock;

use App\Helpers\Logistic\Stock\StockHandler;
use App\Helpers\General\NumberFormatter;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Prompts\Output\ConsoleOutput;
use Tests\TestCase;

class UpdateSubstractStockTest extends TestCase
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

            StockHandler::addStock(
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
            );

            $resultConvert = StockHandler::convertUnitPrice($quantity, $price, $unitDetail->id);
            $convertedQuantity = $resultConvert['quantity'];
            $convertedPrice = $resultConvert['price'];

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

            /*
            | SUBSTRACT STOCK
            */
            $substractQuantity = 5;
            $remarksId = 0;
            $remarksType = 'Test';

            StockHandler::substractStock(
                productId: $product->id,
                companyId: $company->id,
                warehouseId: $warehouse->id,
                quantity: $substractQuantity,
                unitDetailId: $unitDetail->id,
                remarksId: $remarksId,
                remarksType: $remarksType,
            );

            $resultConvert = StockHandler::convertUnitPrice($substractQuantity, $price, $unitDetail->id);
            $convertedSubstractQuantity = $resultConvert['quantity'];

            $this->assertDatabaseHas('product_stocks', [
                'product_id' => $product->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_warehouses', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_companies', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_company_warehouses', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_details', [
                'product_detail_id' => $productDetail->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);


            /*
            | UPDATE SUBSTRACT STOCK
            */
            $newSubstractQuantity = 10;

            StockHandler::updateSubstractStock(
                remarksId: $remarksId,
                remarksType: $remarksType,
                productId: $product->id,
                companyId: $company->id,
                warehouseId: $warehouse->id,
                quantity: $newSubstractQuantity,
                unitDetailId: $unitDetail->id,
            );

            $resultConvert = StockHandler::convertUnitPrice($newSubstractQuantity, $price, $unitDetail->id);
            $convertedSubstractQuantity = $resultConvert['quantity'];

            $this->assertDatabaseHas('product_stocks', [
                'product_id' => $product->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_warehouses', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_companies', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_company_warehouses', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_details', [
                'product_detail_id' => $productDetail->id,
                'quantity' => $convertedQuantity - $convertedSubstractQuantity,
            ]);
        }
    }
}
