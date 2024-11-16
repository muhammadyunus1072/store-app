<?php

namespace App\Repositories\Document\Master\ApprovalConfig;

use App\Repositories\MasterDataRepository;
use App\Models\Document\Master\ApprovalConfigUserStatusApproval;

class ApprovalConfigUserStatusApprovalRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalConfigUserStatusApproval::class;
    }

    public static function createIfNotExist($data)
    {
        $check = ApprovalConfigUserStatusApproval::where('approval_config_user_id', $data['approval_config_user_id'])
            ->where('status_approval_id', $data['status_approval_id'])
            ->first();

        if ($check) {
            return;
        }

        self::create($data);
    }

    public static function deleteExcept($approvalConfigUserId, $ids)
    {
        $deletedData = ApprovalConfigUserStatusApproval::where('approval_config_user_id', $approvalConfigUserId)
            ->whereNotIn('status_approval_id', $ids)
            ->get();

        foreach ($deletedData as $item) {
            $item->delete();
        }
    }
}
