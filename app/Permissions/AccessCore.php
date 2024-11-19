<?php

namespace App\Permissions;

class AccessCore
{
    const SETTING = "setting_core";
    const DASHBOARD = "dashboard";
    const USER = "user";
    const PERMISSION = "permission";
    const ROLE = "role";
    const COMPANY = "company";
    const IMPORT_DATA = "import_data_logistic";

    const ALL = [
        self::SETTING,
        self::DASHBOARD,
        self::USER,
        self::PERMISSION,
        self::ROLE,
        self::COMPANY,
        self::IMPORT_DATA,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::DASHBOARD => [PermissionHelper::TYPE_READ],
        self::USER => PermissionHelper::TYPE_ALL,
        self::ROLE => PermissionHelper::TYPE_ALL,
        self::PERMISSION => PermissionHelper::TYPE_ALL,
        self::COMPANY => PermissionHelper::TYPE_ALL,
        self::IMPORT_DATA => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Utama",
        self::DASHBOARD => "Dashboard",
        self::USER => "Pengguna",
        self::PERMISSION => "Akses",
        self::ROLE => "Jabatan",
        self::COMPANY => "Perusahaan",
        self::IMPORT_DATA => "Import Data",
    ];
}
