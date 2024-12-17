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
                $permissionText = PermissionHelper::transform($access, $type);
                $permission = Permission::where('name', $permissionText)->first();
                if (empty($permission)) {
                    Permission::create(['name' => $permissionText]);
                    $this->command->outputComponents()->info("PERMISSION: $permissionText (NEW)");
                } else {
                    $this->command->outputComponents()->info("PERMISSION: $permissionText");
                }
            }
        }
    }
}
