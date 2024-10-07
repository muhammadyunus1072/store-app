<?php

namespace App\Console\Commands\Logistic;

use App\Helpers\Logistic\StockHelper;
use Illuminate\Console\Command;

class CalculateProductStockDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-product-stock-detail {--productDetailId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate All Product Stock Detail';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        StockHelper::calculateStockDetail($this->option('productDetailId'));
    }
}
