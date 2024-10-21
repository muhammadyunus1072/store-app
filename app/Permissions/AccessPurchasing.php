<?php

namespace App\Permissions;

class AccessPurchasing
{
    const SUPPLIER = "supplier";
    const CATEGORY_SUPPLIER = "category_supplier";

    const ALL = [
        self::SUPPLIER,
        self::CATEGORY_SUPPLIER,
    ];

    const TYPE_ALL = [
        self::SUPPLIER => PermissionHelper::TYPE_ALL,
        self::CATEGORY_SUPPLIER => PermissionHelper::TYPE_ALL,
    ];

    const TRANSLATE = [
        self::SUPPLIER => "Supplier",
        self::CATEGORY_SUPPLIER => "Kategori Supplier",
    ];
}
