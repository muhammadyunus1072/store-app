<?php

namespace App\Repositories\Purchasing\Transaction\PurchaseOrder;

use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Repositories\MasterDataRepository;

class PurchaseOrderRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PurchaseOrder::class;
    }

    public static function datatable()
    {
        return PurchaseOrder::query();
    }
}
