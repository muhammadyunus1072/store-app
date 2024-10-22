<?php

namespace App\Repositories\Purchasing\Transaction\PurchaseOrder;

use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;
use App\Repositories\MasterDataRepository;

class PurchaseOrderProductRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PurchaseOrderProduct::class;
    }

    public static function datatable()
    {
        return PurchaseOrderProduct::query();
    }
}
