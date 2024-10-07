<?php

namespace App\Repositories\Logistic\Transaction\GoodReceive;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProductAttachment;

class GoodReceiveProductAttachmentRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return GoodReceiveProductAttachment::class;
    }

    public static function datatable()
    {
        return GoodReceiveProductAttachment::query();
    }
}
