<?php

namespace App\Console\Commands\Logistic;

use App\Helpers\Logistic\StockHelper;
use Illuminate\Console\Command;

class CalculateProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-product-stock {--productId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate All Product Stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        StockHelper::calculateStock($this->option('productId'));
    }
}
