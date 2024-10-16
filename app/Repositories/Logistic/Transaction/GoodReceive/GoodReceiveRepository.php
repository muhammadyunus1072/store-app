<?php

namespace App\Repositories\Logistic\Transaction\GoodReceive;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceive;

class GoodReceiveRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return GoodReceive::class;
    }

    public static function findWithDetails($id)
    {
        return GoodReceive::with(
            [
                'supplier',
                'warehouse',
                'goodReceiveProducts',
                'goodReceiveProducts.ppn',
                'goodReceiveProducts.pph',
                'goodReceiveProducts.goodReceiveProductAttachments',
            ]
        )->where('id', $id)->first();
    }

    public static function datatable()
    {
        return GoodReceive::query();
    }
}
