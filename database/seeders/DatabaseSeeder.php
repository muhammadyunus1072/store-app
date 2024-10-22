<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Core\CompanySeeder;
use Database\Seeders\Document\StatusApprovalSeeder;
use Database\Seeders\Finance\TaxSeeder;
use Database\Seeders\Logistic\CategoryProductSeeder;
use Database\Seeders\Logistic\CategorySupplierSeeder;
use Database\Seeders\Logistic\ProductSeeder;
use Database\Seeders\Logistic\SupplierSeeder;
use Database\Seeders\Logistic\UnitSeeder;
use Database\Seeders\Logistic\WarehouseSeeder;
use Database\Seeders\Core\RolesAndPermissionsSeeder;
use Database\Seeders\Core\UserSeeder;
use Database\Seeders\Core\UserWarehouseSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core Seeder
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            // UserCompanySeeder::class,
            UserWarehouseSeeder::class,
            CompanySeeder::class,
            // CompanyWarehouseSeeder::class,
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
            SupplierSeeder::class,
            CategorySupplierSeeder::class,
            WarehouseSeeder::class,
            CategoryProductSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            Logistic\SettingSeeder::class,
        ]);

        // Purchasing Seeder
        $this->call([
            Purchasing\SettingSeeder::class,
        ]);
    }
}
