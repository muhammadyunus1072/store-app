<?php

namespace App\Permissions;

class AccessLogistic
{
    const SETTING = "setting_logistic";
    const UNIT = "unit";
    const CATEGORY_PRODUCT = "category_product";
    const PRODUCT = "product";
    const WAREHOUSE = "warehouse";
    const STOCK_REQUEST = "stock_request";
    const STOCK_EXPENSE = "stock_expense";

    const ALL = [
        self::SETTING,
        self::UNIT,
        self::CATEGORY_PRODUCT,
        self::PRODUCT,
        self::WAREHOUSE,
        self::STOCK_REQUEST,
        self::STOCK_EXPENSE,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::UNIT => PermissionHelper::TYPE_ALL,
        self::CATEGORY_PRODUCT => PermissionHelper::TYPE_ALL,
        self::PRODUCT => PermissionHelper::TYPE_ALL,
        self::WAREHOUSE => PermissionHelper::TYPE_ALL,
        self::STOCK_REQUEST => PermissionHelper::TYPE_ALL,
        self::STOCK_EXPENSE => PermissionHelper::TYPE_ALL,
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Logistic",
        self::UNIT => "Satuan",
        self::CATEGORY_PRODUCT => "Kategori Produk",
        self::PRODUCT => "Produk",
        self::WAREHOUSE => "Gudang",
        self::STOCK_REQUEST => "Permintaan Barang",
        self::STOCK_EXPENSE => "Pengeluaran Barang",
    ];
}
