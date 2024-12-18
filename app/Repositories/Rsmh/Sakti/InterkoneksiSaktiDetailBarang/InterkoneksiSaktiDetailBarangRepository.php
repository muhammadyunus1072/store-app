<?php

namespace App\Repositories\Rsmh\Sakti\InterkoneksiSaktiDetailBarang;

use App\Models\Rsmh\Sakti\InterkoneksiSaktiDetailBarang;
use App\Repositories\MasterDataRepository;

class InterkoneksiSaktiDetailBarangRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return InterkoneksiSaktiDetailBarang::class;
    }

    public static function findLastKodeUpload()
    {
        return InterkoneksiSaktiDetailBarang::select('kode_upload')->orderBy('kode_upload', 'DESC')->first();
    }

    public static function datatable()
    {
        return InterkoneksiSaktiDetailBarang::query();
    }
}
