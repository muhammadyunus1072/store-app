<?php

namespace App\Repositories\Rsmh\Sync\SyncSupplier;

use App\Models\Rsmh\Sync\SyncSupplier;
use App\Repositories\MasterDataRepository;

class SyncSupplierRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return SyncSupplier::class;
    }
}
