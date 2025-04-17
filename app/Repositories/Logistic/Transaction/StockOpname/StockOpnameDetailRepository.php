<?php

namespace App\Repositories\Logistic\Transaction\StockOpname;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockOpname\StockOpnameDetail;

class StockOpnameDetailRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockOpnameDetail::class;
    }
}
