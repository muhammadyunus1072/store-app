<?php

namespace App\Repositories\Logistic\Transaction\TransactionStock;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStockProductAttachment;

class TransactionStockProductAttachmentRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return TransactionStockProductAttachment::class;
    }
}
