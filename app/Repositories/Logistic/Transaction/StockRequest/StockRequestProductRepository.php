<?php

namespace App\Repositories\Logistic\Transaction\StockRequest;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;

class StockRequestProductRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockRequestProduct::class;
    }
}
