<?php

namespace App\Repositories\Document\Transaction;

use App\Models\Document\Transaction\ApprovalStatus;
use App\Repositories\MasterDataRepository;

class ApprovalStatusRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalStatus::class;
    }

    public static function datatable($approvalId)
    {
        return ApprovalStatus::with('user')
            ->where('approval_id', $approvalId)
            ->orderBy('id', 'DESC');
    }
}
