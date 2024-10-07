<?php

namespace App\Console\Commands\Logistic;

use App\Helpers\Logistic\StockHelper;
use Illuminate\Console\Command;

class CalculateProductStockWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-product-stock-warehouse {--productId=} {--warehouseId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate All Product Stock Warehouse';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        StockHelper::calculateStockWarehouse($this->option('productId'), $this->option('warehouseId'));
    }
}
