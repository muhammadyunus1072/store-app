<?php

namespace App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa;

use App\Models\Rsmh\Sakti\InterkoneksiSaktiCoa;
use App\Repositories\MasterDataRepository;

class InterkoneksiSaktiCoaRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return InterkoneksiSaktiCoa::class;
    }

    public static function datatable()
    {
        return InterkoneksiSaktiCoa::query();
    }
}
