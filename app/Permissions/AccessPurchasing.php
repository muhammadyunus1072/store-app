<?php

namespace App\Permissions;

class AccessPurchasing
{
    const SETTING = "setting_purchasing";
    const SUPPLIER = "supplier";
    const CATEGORY_SUPPLIER = "category_supplier";
    const PURCHASE_ORDER = "purchase_order";

    const ALL = [
        self::SETTING,
        self::SUPPLIER,
        self::CATEGORY_SUPPLIER,
        self::PURCHASE_ORDER,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::SUPPLIER => PermissionHelper::TYPE_ALL,
        self::CATEGORY_SUPPLIER => PermissionHelper::TYPE_ALL,
        self::PURCHASE_ORDER => PermissionHelper::TYPE_ALL,
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Pembelian",
        self::SUPPLIER => "Supplier",
        self::CATEGORY_SUPPLIER => "Kategori Supplier",
        self::PURCHASE_ORDER => "Pembelian",
    ];
}
