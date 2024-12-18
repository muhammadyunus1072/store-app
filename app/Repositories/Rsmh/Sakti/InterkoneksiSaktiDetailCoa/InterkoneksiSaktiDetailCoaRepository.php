<?php

namespace App\Repositories\Rsmh\Sakti\InterkoneksiSaktiDetailCoa;

use App\Models\Rsmh\Sakti\InterkoneksiSaktiDetailCoa;
use App\Repositories\MasterDataRepository;

class InterkoneksiSaktiDetailCoaRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return InterkoneksiSaktiDetailCoa::class;
    }

    public static function findLastKodeUpload()
    {
        return InterkoneksiSaktiDetailCoa::select('kode_upload')->orderBy('kode_upload', 'DESC')->first();
    }

    public static function datatable()
    {
        return InterkoneksiSaktiDetailCoa::query();
    }
}
