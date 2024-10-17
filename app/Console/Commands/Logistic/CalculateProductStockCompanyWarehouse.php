<?php

namespace App\Console\Commands\Logistic;

use App\Helpers\Logistic\Stock\StockHandler;
use Illuminate\Console\Command;

class CalculateProductStockCompanyWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-product-stock-company-warehouse {--productId=} {--companyId=} {--warehouseId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate All Product Stock Company Warehouse';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        StockHandler::calculateStockCompanyWarehouse($this->option('productId'), $this->option('companyId'), $this->option('warehouseId'));
    }
}
