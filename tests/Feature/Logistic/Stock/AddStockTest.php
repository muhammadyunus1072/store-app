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

class AddStockTest extends TestCase
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

            $consoleOuput->writeln("");
            $consoleOuput->writeln("=== CONVERTION INFO ===");
            $consoleOuput->writeln("FROM : " . NumberFormatter::format($quantity) . " $unitDetail->name | Rp" . NumberFormatter::format($price) . "/$unitDetail->name");
            $consoleOuput->writeln("TO   : " . NumberFormatter::format($convertedQuantity) . " $mainUnitDetail->name | Rp" . NumberFormatter::format($convertedPrice) . "/$mainUnitDetail->name");

            $this->assertDatabaseHas('product_stocks', [
                'product_id' => $product->id,
                'quantity' => $convertedQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_warehouses', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $convertedQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_companies', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'quantity' => $convertedQuantity,
            ]);

            $this->assertDatabaseHas('product_stock_company_warehouses', [
                'product_id' => $product->id,
                'company_id' => $company->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $convertedQuantity,
            ]);

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
                'quantity' => $convertedQuantity,
            ]);
        }
    }
}
