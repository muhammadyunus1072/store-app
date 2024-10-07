<?php

namespace App\Repositories\Logistic\Transaction\GoodReceive;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProductTax;

class GoodReceiveProductTaxRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return GoodReceiveProductTax::class;
    }
}
