<?php

namespace Database\Seeders\Core;

use Illuminate\Database\Seeder;
use App\Permissions\PermissionHelper;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create role
        $role = Role::create(['name' => "Admin"]);
        foreach (PermissionHelper::ACCESS_TYPE_ALL as $access => $types) {
            foreach ($types as $type) {
                $role->givePermissionTo(PermissionHelper::transform($access, $type));
            }
        }
    }
}
