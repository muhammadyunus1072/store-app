<?php

namespace App\Repositories\Core\User;

use App\Helpers\General\MenuHelper;
use App\Repositories\MasterDataRepository;
use Spatie\Permission\Models\Role;

class RoleRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Role::class;
    }

    public static function update($id, $data)
    {
        $obj = self::find($id);

        MenuHelper::resetCacheByRole($id);

        return $obj->update($data);
    }

    public static function getIdAndNames()
    {
        return Role::select('id', 'name')->orderBy('name')->get();
    }

    public static function datatable()
    {
        return Role::with('permissions');
    }
}
