<?php

namespace App\Repositories\Rsmh\Sync\SyncPengeluaranRt;

use App\Models\Rsmh\Sync\SyncPengeluaranRt;
use App\Repositories\MasterDataRepository;

class SyncPengeluaranRtRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return SyncPengeluaranRt::class;
    }
}
