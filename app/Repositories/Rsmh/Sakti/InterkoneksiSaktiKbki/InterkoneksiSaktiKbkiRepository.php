<?php

namespace App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki;

use App\Models\Rsmh\Sakti\InterkoneksiSaktiKbki;
use App\Repositories\MasterDataRepository;

class InterkoneksiSaktiKbkiRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return InterkoneksiSaktiKbki::class;
    }

    public static function datatable()
    {
        return InterkoneksiSaktiKbki::query();
    }
}
