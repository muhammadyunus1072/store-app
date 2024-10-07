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

    public static function findByUser($approval_id, $user_id)
    {
        return ApprovalUser::where('approval_id', $approval_id)
        ->where('user_id', $user_id)
        ->first();
    }
}
