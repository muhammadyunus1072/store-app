<?php

namespace App\Repositories\Document\Transaction;

use App\Repositories\MasterDataRepository;
use App\Models\Document\Transaction\ApprovalUserStatusApproval;

class ApprovalUserStatusApprovalRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalUserStatusApproval::class;
    }
}
