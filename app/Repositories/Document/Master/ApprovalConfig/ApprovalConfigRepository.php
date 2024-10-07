<?php

namespace App\Repositories\Document\Master\ApprovalConfig;

use Illuminate\Support\Facades\Auth;
use App\Repositories\MasterDataRepository;
use App\Models\Document\Master\ApprovalConfig;

class ApprovalConfigRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalConfig::class;
    }

    public static function findWithDetails($id)
    {
        return ApprovalConfig::with([
            'approvalConfigUsers'
        ])
        ->where('id', $id)
        ->first();
    }
    public static function getByKey($key)
    {
        return ApprovalConfig::with([
            'approvalConfigUsers'
        ])
        ->where('key', $key)
        ->orderBy('priority', 'ASC')
        ->get();
    }

    public static function datatable()
    {
        return ApprovalConfig::query();
    }
}
