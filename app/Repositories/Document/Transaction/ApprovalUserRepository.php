<?php

namespace App\Repositories\Document\Transaction;

use App\Repositories\MasterDataRepository;
use App\Models\Document\Transaction\ApprovalUser;

class ApprovalUserRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalUser::class;
    }

    public static function countMenuNotification($userId)
    {
        return ApprovalUser::where('user_id', $userId)
            ->whereDoesntHave('approvalStatus')
            ->count();
    }

    public static function findNextSubmission($approvalId)
    {
        return ApprovalUser::where('approval_id', $approvalId)
            ->whereDoesntHave('approvalStatus')
            ->orderBy('position', 'ASC')
            ->first();
    }

    public static function findNotSubmitted($approvalId, $userId)
    {
        return ApprovalUser::where('approval_id', $approvalId)
            ->where('user_id', $userId)
            ->whereDoesntHave('approvalStatus')
            ->first();
    }
}
