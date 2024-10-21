<?php

namespace Database\Seeders\Core;

use App\Permissions\PermissionHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        foreach (PermissionHelper::ACCESS_TYPE_ALL as $access => $types) {
            foreach ($types as $type) {
                Permission::create(['name' => PermissionHelper::transform($access, $type)]);
            }
        }

        // create role
        $role = Role::create(['name' => "Admin"]);
        foreach (PermissionHelper::ACCESS_TYPE_ALL as $access => $types) {
            foreach ($types as $type) {
                $role->givePermissionTo(PermissionHelper::transform($access, $type));
            }
        }
    }
}
