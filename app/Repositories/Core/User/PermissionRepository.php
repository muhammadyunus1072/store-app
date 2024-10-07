<?php

namespace App\Repositories\Core\User;

use App\Repositories\MasterDataRepository;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Permission::class;
    }

    public static function findByName($name)
    {
        return Permission::whereName($name)->first();
    }

    public static function getIdAndNames()
    {
        return Permission::select('id', 'name')->orderBy('name')->get();
    }

    public static function datatable()
    {
        return Permission::query();
    }
}
