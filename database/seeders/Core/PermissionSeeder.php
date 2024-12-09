<?php

namespace Database\Seeders\Core;

use App\Permissions\PermissionHelper;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
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
                $permission = Permission::where('name', PermissionHelper::transform($access, $type))->first();
                if (empty($permission)) {
                    Permission::create(['name' => PermissionHelper::transform($access, $type)]);
                }
            }
        }
    }
}
