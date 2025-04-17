<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Core\CompanyDisplayRackSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\Core\RoleSeeder;
use Database\Seeders\Core\UserSeeder;
use Database\Seeders\Finance\TaxSeeder;
use Database\Seeders\Core\CompanySeeder;
use Database\Seeders\Logistic\UnitSeeder;
use Database\Seeders\Core\PermissionSeeder;
use Database\Seeders\Core\UserCompanySeeder;
use Database\Seeders\Logistic\ProductSeeder;
use Database\Seeders\Logistic\SupplierSeeder;
use Database\Seeders\Core\UserWarehouseSeeder;
use Database\Seeders\Logistic\WarehouseSeeder;
use Database\Seeders\Core\CompanyWarehouseSeeder;
use Database\Seeders\Core\UserDisplayRackSeeder;
use Database\Seeders\Document\StatusApprovalSeeder;
use Database\Seeders\Logistic\CategoryProductSeeder;
use Database\Seeders\Logistic\CategorySupplierSeeder;
use Database\Seeders\InterkoneksiSakti\InterkoneksiSaktiSettingSeeder;
use Database\Seeders\Logistic\DisplayRackSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core Seeder
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            UserWarehouseSeeder::class,
            UserDisplayRackSeeder::class,
            CompanySeeder::class,
            UserCompanySeeder::class,
            CompanyWarehouseSeeder::class,
            CompanyDisplayRackSeeder::class,
            Core\SettingSeeder::class,
        ]);

        // Finance Seeder
        $this->call([
            TaxSeeder::class,
        ]);

        // Document Seeder
        $this->call([
            StatusApprovalSeeder::class,
        ]);

        // Logistic Seeder
        $this->call([
            WarehouseSeeder::class,
            DisplayRackSeeder::class,
            CategoryProductSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            Logistic\SettingSeeder::class,
        ]);

        // Purchasing Seeder
        $this->call([
            SupplierSeeder::class,
            CategorySupplierSeeder::class,
            Purchasing\SettingSeeder::class,
        ]);
    }
}
