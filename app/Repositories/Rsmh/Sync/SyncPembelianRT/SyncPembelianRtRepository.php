<?php

namespace App\Repositories\Rsmh\Sync\SyncPembelianRt;

use App\Models\Rsmh\Sync\SyncPembelianRt;
use App\Repositories\MasterDataRepository;

class SyncPembelianRtRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return SyncPembelianRt::class;
    }
}
