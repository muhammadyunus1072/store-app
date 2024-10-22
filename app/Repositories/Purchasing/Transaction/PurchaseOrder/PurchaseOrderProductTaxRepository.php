<?php

namespace App\Repositories\Purchasing\Transaction\PurchaseOrder;

use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTax;
use App\Repositories\MasterDataRepository;

class PurchaseOrderProductTaxRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PurchaseOrderProductTax::class;
    }
}
