<?php

namespace App\Repositories\Document\Transaction;

use Illuminate\Support\Facades\Auth;
use App\Repositories\MasterDataRepository;
use App\Models\Document\Transaction\Approval;
use App\Repositories\Document\Transaction\ApprovalUserRepository;

class ApprovalRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Approval::class;
    }

    public static function findWithDetails($id)
    {
        return Approval::with([
            'creator',    
            'approvalUsers',    
            'approvalUsers.user',
            'approvalUserHistories.user',
        ]
        )->where('id', $id)->first();
    }

    public static function viewShow($id)
    {
        $user_id = Auth::id();
        $approval = self::find($id);
        $obj = app($approval->remarks_type)->find($approval->remarks_id);
        return $obj->approvalViewShow($approval->approvalUser);
    }

    public static function datatable()
    {
        return Approval::with('approvalUsers', 'approvalUserHistories')->whereHas('approvalUsers', function($query)
            {
                $query->where('user_id', Auth::id());
            }
        );
    }
}
