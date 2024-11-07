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

    const CURRENT_STOCK = "current_stock";
    const CURRENT_STOCK_DETAIL = "current_stock_detail";
    const CURRENT_STOCK_WAREHOUSE = "current_stock_warehouse";
    const CURRENT_STOCK_WAREHOUSE_DETAIL = "current_stock_warehouse_detail";
    
    const STOCK_CARD = "stock_card";
    const STOCK_CARD_DETAIL = "stock_card_detail";
    const STOCK_CARD_WAREHOUSE = "stock_card_warehouse";
    const STOCK_CARD_WAREHOUSE_DETAIL = "stock_card_warehouse_detail";

    const ALL = [
        self::SETTING,
        self::UNIT,
        self::CATEGORY_PRODUCT,
        self::PRODUCT,
        self::WAREHOUSE,
        self::STOCK_REQUEST,
        self::STOCK_EXPENSE,

        self::CURRENT_STOCK,
        self::CURRENT_STOCK_DETAIL,
        self::CURRENT_STOCK_WAREHOUSE,
        self::CURRENT_STOCK_WAREHOUSE_DETAIL,

        self::STOCK_CARD,
        self::STOCK_CARD_DETAIL,
        self::STOCK_CARD_WAREHOUSE,
        self::STOCK_CARD_WAREHOUSE_DETAIL,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::UNIT => PermissionHelper::TYPE_ALL,
        self::CATEGORY_PRODUCT => PermissionHelper::TYPE_ALL,
        self::PRODUCT => PermissionHelper::TYPE_ALL,
        self::WAREHOUSE => PermissionHelper::TYPE_ALL,
        self::STOCK_REQUEST => PermissionHelper::TYPE_ALL,
        self::STOCK_EXPENSE => PermissionHelper::TYPE_ALL,

        self::CURRENT_STOCK => [PermissionHelper::TYPE_READ],
        self::CURRENT_STOCK_DETAIL => [PermissionHelper::TYPE_READ],
        self::CURRENT_STOCK_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::CURRENT_STOCK_WAREHOUSE_DETAIL => [PermissionHelper::TYPE_READ],

        self::STOCK_CARD => [PermissionHelper::TYPE_READ],
        self::STOCK_CARD_DETAIL => [PermissionHelper::TYPE_READ],
        self::STOCK_CARD_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::STOCK_CARD_WAREHOUSE_DETAIL => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Logistic",
        self::UNIT => "Satuan",
        self::CATEGORY_PRODUCT => "Kategori Produk",
        self::PRODUCT => "Produk",
        self::WAREHOUSE => "Gudang",
        self::STOCK_REQUEST => "Permintaan Barang",
        self::STOCK_EXPENSE => "Pengeluaran Barang",

        self::CURRENT_STOCK => "Stok Akhir",
        self::CURRENT_STOCK_DETAIL => "Stok Akhir Detail",
        self::CURRENT_STOCK_WAREHOUSE => "Stok Akhir Gudang",
        self::CURRENT_STOCK_WAREHOUSE_DETAIL => "Stok Akhir Detail Gudang",

        self::STOCK_CARD => "Kartu Stok",
        self::STOCK_CARD_DETAIL => "Kartu Stok Detail",
        self::STOCK_CARD_WAREHOUSE => "Kartu Stok Gudang",
        self::STOCK_CARD_WAREHOUSE_DETAIL => "Kartu Stok Detail Gudang",
    ];
}
