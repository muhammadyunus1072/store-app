<?php

namespace App\Repositories\Document\Master\ApprovalConfig;

use Illuminate\Support\Facades\Auth;
use App\Repositories\MasterDataRepository;
use App\Models\Document\Master\ApprovalConfigUser;

class ApprovalConfigUserRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ApprovalConfigUser::class;
    }
}
