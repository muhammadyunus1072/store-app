<?php

namespace App\Repositories\Rsmh\Sync\SyncWarehouse;

use App\Models\Rsmh\Sync\SyncWarehouse;
use App\Repositories\MasterDataRepository;

class SyncWarehouseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return SyncWarehouse::class;
    }
}
