<?php

namespace App\Console\Commands\Logistic;

use App\Helpers\Logistic\StockHelper;
use Illuminate\Console\Command;

class CalculateProductStockCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-product-stock-company {--productId=} {--companyId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate All Product Stock Company';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        StockHelper::calculateStockCompany($this->option('productId'), $this->option('companyId'));
    }
}
