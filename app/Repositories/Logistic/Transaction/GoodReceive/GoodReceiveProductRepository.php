<?php

namespace App\Repositories\Logistic\Transaction\GoodReceive;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProduct;

class GoodReceiveProductRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return GoodReceiveProduct::class;
    }

    public static function datatable()
    {
        return GoodReceiveProduct::query();
    }
}
