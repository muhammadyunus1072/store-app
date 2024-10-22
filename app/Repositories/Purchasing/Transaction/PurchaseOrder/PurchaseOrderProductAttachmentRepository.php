<?php

namespace App\Repositories\Purchasing\Transaction\PurchaseOrder;

use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductAttachment;
use App\Repositories\MasterDataRepository;

class PurchaseOrderProductAttachmentRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PurchaseOrderProductAttachment::class;
    }

    public static function datatable()
    {
        return PurchaseOrderProductAttachment::query();
    }
}
