<?php

namespace App\Repositories\Document\Transaction;

use App\Repositories\MasterDataRepository;
use App\Models\Document\Transaction\ApprovalHistory;

class ApprovalHistoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalHistory::class;
    }

    public static function datatable($approval_id)
    {
        return ApprovalHistory::where('approval_id', $approval_id);
    }

    public static function findByUser($approval_id, $user_id, $status_id)
    {
        return ApprovalHistory::where('approval_id', $approval_id)
        ->where('user_id', $user_id)
        ->where('status_id', $status_id)
        ->first();
    }
}
