<?php

namespace App\Permissions;

class AccessLogistic
{
    const SETTING = "setting_logistic";
    const UNIT = "unit";
    const CATEGORY_PRODUCT = "category_product";
    const PRODUCT = "product";
    // const WAREHOUSE = "warehouse";
    const STOCK_REQUEST = "stock_request";
    // const STOCK_EXPENSE = "stock_expense";
    const STOCK_OPNAME = "stock_opname";

    // const I_STOCK_EXPENSE = "i_stock_expense";
    // const I_STOCK_REQUEST = "i_stock_request";
    const I_MASTER_DATA = "i_master_data_logistic";

    const R_CURRENT_STOCK = "r_current_stock";
    // const R_CURRENT_STOCK_DETAIL = "r_current_stock_detail";
    // const R_HISTORY_STOCK = "r_history_stock";
    // const R_HISTORY_STOCK_DETAIL = "r_history_stock_detail";
    // const R_STOCK_EXPENSE = "r_stock_expense";
    // const R_STOCK_EXPIRED = "r_stock_expired";

    const R_CURRENT_STOCK_WAREHOUSE = "r_current_stock_warehouse";
    const R_CURRENT_STOCK_DISPLAY_RACK = "r_current_stock_display_rack";
    // const R_CURRENT_STOCK_DETAIL_WAREHOUSE = "r_current_stock_detail_warehouse";
    // const R_HISTORY_STOCK_WAREHOUSE = "r_history_stock_warehouse";
    // const R_HISTORY_STOCK_DETAIL_WAREHOUSE = "r_history_stock_detail_warehouse";
    // const R_STOCK_EXPENSE_WAREHOUSE = "r_stock_expense_warehouse";
    // const R_STOCK_EXPIRED_WAREHOUSE = "r_stock_expired_warehouse";
    // const R_STOCK_REQUEST_IN_WAREHOUSE = "r_stock_request_in_warehouse";
    // const R_STOCK_REQUEST_OUT_WAREHOUSE = "r_stock_request_out_warehouse";

    const ALL = [
        self::SETTING,
        self::UNIT,
        self::CATEGORY_PRODUCT,
        self::PRODUCT,
        // self::WAREHOUSE,
        self::STOCK_REQUEST,
        // self::STOCK_EXPENSE,
        self::STOCK_OPNAME,

        // self::I_STOCK_EXPENSE,
        // self::I_STOCK_REQUEST,
        self::I_MASTER_DATA,

        self::R_CURRENT_STOCK,
        // self::R_CURRENT_STOCK_DETAIL,
        // self::R_HISTORY_STOCK,
        // self::R_HISTORY_STOCK_DETAIL,
        // self::R_STOCK_EXPENSE,
        // self::R_STOCK_EXPIRED,

        self::R_CURRENT_STOCK_WAREHOUSE,
        self::R_CURRENT_STOCK_DISPLAY_RACK,
        // self::R_CURRENT_STOCK_DETAIL_WAREHOUSE,
        // self::R_HISTORY_STOCK_WAREHOUSE,
        // self::R_HISTORY_STOCK_DETAIL_WAREHOUSE,
        // self::R_STOCK_EXPENSE_WAREHOUSE,
        // self::R_STOCK_EXPIRED_WAREHOUSE,
        // self::R_STOCK_REQUEST_IN_WAREHOUSE,
        // self::R_STOCK_REQUEST_OUT_WAREHOUSE,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::UNIT => PermissionHelper::TYPE_ALL,
        self::CATEGORY_PRODUCT => PermissionHelper::TYPE_ALL,
        self::PRODUCT => PermissionHelper::TYPE_ALL,
        // self::WAREHOUSE => PermissionHelper::TYPE_ALL,
        self::STOCK_REQUEST => PermissionHelper::TYPE_ALL,
        // self::STOCK_EXPENSE => PermissionHelper::TYPE_ALL,
        self::STOCK_OPNAME => PermissionHelper::TYPE_ALL,

        self::I_MASTER_DATA => [PermissionHelper::TYPE_READ],
        // self::I_STOCK_EXPENSE => [PermissionHelper::TYPE_READ],
        // self::I_STOCK_REQUEST => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_DELETE],

        self::R_CURRENT_STOCK => [PermissionHelper::TYPE_READ],
        // self::R_CURRENT_STOCK_DETAIL => [PermissionHelper::TYPE_READ],
        // self::R_HISTORY_STOCK => [PermissionHelper::TYPE_READ],
        // self::R_HISTORY_STOCK_DETAIL => [PermissionHelper::TYPE_READ],
        // self::R_STOCK_EXPENSE => [PermissionHelper::TYPE_READ],
        // self::R_STOCK_EXPIRED => [PermissionHelper::TYPE_READ],

        self::R_CURRENT_STOCK_WAREHOUSE => [PermissionHelper::TYPE_READ],
        self::R_CURRENT_STOCK_DISPLAY_RACK => [PermissionHelper::TYPE_READ],
        // self::R_CURRENT_STOCK_DETAIL_WAREHOUSE => [PermissionHelper::TYPE_READ],
        // self::R_HISTORY_STOCK_WAREHOUSE => [PermissionHelper::TYPE_READ],
        // self::R_HISTORY_STOCK_DETAIL_WAREHOUSE => [PermissionHelper::TYPE_READ],
        // self::R_STOCK_EXPENSE_WAREHOUSE => [PermissionHelper::TYPE_READ],
        // self::R_STOCK_EXPIRED_WAREHOUSE => [PermissionHelper::TYPE_READ],
        // self::R_STOCK_REQUEST_IN_WAREHOUSE => [PermissionHelper::TYPE_READ],
        // self::R_STOCK_REQUEST_OUT_WAREHOUSE => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Logistic",
        self::UNIT => "Satuan",
        self::CATEGORY_PRODUCT => "Kategori Produk",
        self::PRODUCT => "Produk",
        // self::WAREHOUSE => "Gudang",
        self::STOCK_REQUEST => "Permintaan Barang",
        // self::STOCK_EXPENSE => "Pengeluaran Barang",
        self::STOCK_OPNAME => "Stok Opname",

        self::I_MASTER_DATA => "Import Data - Master Data",
        // self::I_STOCK_EXPENSE => "Import Data - Pengeluaran Barang",
        // self::I_STOCK_REQUEST => "Import Data - Permintaan Barang",

        self::R_CURRENT_STOCK => "Laporan - Stok Akhir",
        // self::R_CURRENT_STOCK_DETAIL => "Laporan - Stok Akhir Detail",
        // self::R_HISTORY_STOCK => "Laporan - Kartu Stok",
        // self::R_HISTORY_STOCK_DETAIL => "Laporan - Kartu Stok Detail",
        // self::R_STOCK_EXPENSE => "Laporan - Pengeluaran",
        // self::R_STOCK_EXPIRED => "Laporan - Stok Expired",

        self::R_CURRENT_STOCK_WAREHOUSE => "Laporan - Stok Akhir (Gudang)",
        self::R_CURRENT_STOCK_DISPLAY_RACK => "Laporan - Stok Akhir (Display Rak)",
        // self::R_CURRENT_STOCK_DETAIL_WAREHOUSE => "Laporan - Stok Akhir Detail (Per Gudang)",
        // self::R_HISTORY_STOCK_WAREHOUSE => "Laporan - Kartu Stok (Per Gudang)",
        // self::R_HISTORY_STOCK_DETAIL_WAREHOUSE => "Laporan - Kartu Stok Detail (Per Gudang)",
        // self::R_STOCK_EXPENSE_WAREHOUSE => "Laporan - Pengeluaran (Per Gudang)",
        // self::R_STOCK_EXPIRED_WAREHOUSE => "Laporan - Stok Expired (Per Gudang)",
        // self::R_STOCK_REQUEST_IN_WAREHOUSE => "Laporan - Transfer Masuk (Per Gudang)",
        // self::R_STOCK_REQUEST_OUT_WAREHOUSE => "Laporan - Transfer Keluar (Per Gudang)",
    ];
}
