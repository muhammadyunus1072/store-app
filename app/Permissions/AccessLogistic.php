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

    const R_CURRENT_STOCK = "r_current_stock";
    const R_CURRENT_STOCK_DETAIL = "r_current_stock_detail";
    const R_HISTORY_STOCK = "r_history_stock";
    const R_HISTORY_STOCK_DETAIL = "r_history_stock_detail";
    const R_STOCK_EXPENSE = "r_stock_expense";
    const R_STOCK_EXPIRED = "r_stock_expired";

    const R_CURRENT_STOCK_WAREHOUSE = "r_current_stock_warehouse";
    const R_CURRENT_STOCK_DETAIL_WAREHOUSE = "r_current_stock_detail_warehouse";
    const R_HISTORY_STOCK_WAREHOUSE = "r_history_stock_warehouse";
    const R_HISTORY_STOCK_DETAIL_WAREHOUSE = "r_history_stock_detail_warehouse";
    const R_STOCK_EXPENSE_WAREHOUSE = "r_stock_expense_warehouse";
    const R_STOCK_EXPIRED_WAREHOUSE = "r_stock_expired_warehouse";

    const ALL = [
        self::SETTING,
        self::UNIT,
        self::CATEGORY_PRODUCT,
        self::PRODUCT,
        self::WAREHOUSE,
        self::STOCK_REQUEST,
        self::STOCK_EXPENSE,

        self::R_CURRENT_STOCK,
        self::R_CURRENT_STOCK_DETAIL,
        self::R_HISTORY_STOCK,
        self::R_HISTORY_STOCK_DETAIL,
        self::R_STOCK_EXPENSE,
        self::R_STOCK_EXPIRED,

        self::R_CURRENT_STOCK_WAREHOUSE,
        self::R_CURRENT_STOCK_DETAIL_WAREHOUSE,
        self::R_HISTORY_STOCK_WAREHOUSE,
        self::R_HISTORY_STOCK_DETAIL_WAREHOUSE,
        self::R_STOCK_EXPENSE_WAREHOUSE,
        self::R_STOCK_EXPIRED_WAREHOUSE,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::UNIT => PermissionHelper::TYPE_ALL,
        self::CATEGORY_PRODUCT => PermissionHelper::TYPE_ALL,
        self::PRODUCT => PermissionHelper::TYPE_ALL,
        self::WAREHOUSE => PermissionHelper::TYPE_ALL,
        self::STOCK_REQUEST => PermissionHelper::TYPE_ALL,
        self::STOCK_EXPENSE => PermissionHelper::TYPE_ALL,

        self::R_CURRENT_STOCK => [PermissionHelper::TYPE_READ],
        self::R_CURRENT_STOCK_DETAIL => [PermissionHelper::TYPE_READ],
        self::R_HISTORY_STOCK => [PermissionHelper::TYPE_READ],
        self::R_HISTORY_STOCK_DETAIL => [PermissionHelper::TYPE_READ],
        self::R_STOCK_EXPENSE => [PermissionHelper::TYPE_READ],
        self::R_STOCK_EXPIRED => [PermissionHelper::TYPE_READ],
        
        self::R_CURRENT_STOCK_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::R_CURRENT_STOCK_DETAIL_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::R_HISTORY_STOCK_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::R_HISTORY_STOCK_DETAIL_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::R_STOCK_EXPENSE_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::R_STOCK_EXPIRED_WAREHOUSE => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Logistic",
        self::UNIT => "Satuan",
        self::CATEGORY_PRODUCT => "Kategori Produk",
        self::PRODUCT => "Produk",
        self::WAREHOUSE => "Gudang",
        self::STOCK_REQUEST => "Permintaan Barang",
        self::STOCK_EXPENSE => "Pengeluaran Barang",

        self::R_CURRENT_STOCK => "Laporan - Stok Akhir",
        self::R_CURRENT_STOCK_DETAIL => "Laporan - Stok Akhir Detail",
        self::R_HISTORY_STOCK => "Laporan - Kartu Stok",
        self::R_HISTORY_STOCK_DETAIL => "Laporan - Kartu Stok Detail",
        self::R_STOCK_EXPENSE => "Laporan - Pengeluaran",
        self::R_STOCK_EXPIRED => "Laporan - Stok Expired",

        self::R_CURRENT_STOCK_WAREHOUSE => "Laporan - Stok Akhir (Per Gudang)",
        self::R_CURRENT_STOCK_DETAIL_WAREHOUSE => "Laporan - Stok Akhir Detail (Per Gudang)",
        self::R_HISTORY_STOCK_WAREHOUSE => "Laporan - Kartu Stok (Per Gudang)",
        self::R_HISTORY_STOCK_DETAIL_WAREHOUSE => "Laporan - Kartu Stok Detail (Per Gudang)",
        self::R_STOCK_EXPENSE_WAREHOUSE => "Laporan - Pengeluaran (Per Gudang)",
        self::R_STOCK_EXPIRED_WAREHOUSE => "Laporan - Stok Expired (Per Gudang)",
    ];
}
