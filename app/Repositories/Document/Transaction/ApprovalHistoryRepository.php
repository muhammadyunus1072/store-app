<?php

namespace App\Repositories\Document\Transaction;

use App\Repositories\MasterDataRepository;
use App\Models\Document\Transaction\ApprovalUserHistory;

class ApprovalHistoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalUserHistory::class;
    }

    public static function datatable($approval_id)
    {
        return ApprovalUserHistory::where('approval_user_id', $approval_id);
    }

    public static function findByUser($approval_id, $user_id, $status_id)
    {
        return ApprovalUserHistory::where('approval_id', $approval_id)
        ->where('user_id', $user_id)
        ->where('status_id', $status_id)
        ->first();
    }
}
