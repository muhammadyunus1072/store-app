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

    public static function findByName($permissionName)
    {
        return Permission::where('name', '=', $permissionName)->first();
    }

    public static function datatable()
    {
        return Permission::query();
    }
}
