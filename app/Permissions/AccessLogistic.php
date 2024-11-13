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
    const CURRENT_STOCK_DETAIL_WAREHOUSE = "current_stock_detail_warehouse";
    
    const HISTORY_STOCK = "history_stock";
    const HISTORY_STOCK_DETAIL = "history_stock_detail";
    const HISTORY_STOCK_WAREHOUSE = "history_stock_warehouse";
    const HISTORY_STOCK_DETAIL_WAREHOUSE = "history_stock_detail_warehouse";

    const STOCK_EXPENSE_REPORT = "stock_expense_report";
    const STOCK_EXPENSE_WAREHOUSE = "stock_expense_warehouse";

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
        self::CURRENT_STOCK_DETAIL_WAREHOUSE,

        self::HISTORY_STOCK,
        self::HISTORY_STOCK_DETAIL,
        self::HISTORY_STOCK_WAREHOUSE,
        self::HISTORY_STOCK_DETAIL_WAREHOUSE,

        self::STOCK_EXPENSE_REPORT,
        self::STOCK_EXPENSE_WAREHOUSE,
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
        self::CURRENT_STOCK_DETAIL_WAREHOUSE => [PermissionHelper::TYPE_READ],

        self::HISTORY_STOCK => [PermissionHelper::TYPE_READ],
        self::HISTORY_STOCK_DETAIL => [PermissionHelper::TYPE_READ],
        self::HISTORY_STOCK_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::HISTORY_STOCK_DETAIL_WAREHOUSE => [PermissionHelper::TYPE_READ],

        self::STOCK_EXPENSE_REPORT => [PermissionHelper::TYPE_READ],
        self::STOCK_EXPENSE_WAREHOUSE => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Logistic",
        self::UNIT => "Satuan",
        self::CATEGORY_PRODUCT => "Kategori Produk",
        self::PRODUCT => "Produk",
        self::WAREHOUSE => "Gudang",
        self::STOCK_REQUEST => "Permintaan Barang",
        self::STOCK_EXPENSE => "Pengeluaran Barang",

        self::CURRENT_STOCK => "Laporan Stok Akhir",
        self::CURRENT_STOCK_DETAIL => "Laporan Stok Akhir Detail",
        self::CURRENT_STOCK_WAREHOUSE => "Laporan Stok Akhir Gudang",
        self::CURRENT_STOCK_DETAIL_WAREHOUSE => "Laporan Stok Akhir Detail Gudang",

        self::HISTORY_STOCK => "Laporan Kartu Stok",
        self::HISTORY_STOCK_DETAIL => "Laporan Kartu Stok Detail",
        self::HISTORY_STOCK_WAREHOUSE => "Laporan Kartu Stok Gudang",
        self::HISTORY_STOCK_DETAIL_WAREHOUSE => "Laporan Kartu Stok Detail Gudang",

        self::STOCK_EXPENSE_REPORT => "Laporan Pengeluaran",
        self::STOCK_EXPENSE_WAREHOUSE => "Laporan Pengeluaran Gudang",
    ];
}
