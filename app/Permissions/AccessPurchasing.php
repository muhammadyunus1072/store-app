<?php

namespace App\Permissions;

class AccessPurchasing
{
    const SETTING = "setting_purchasing";
    const SUPPLIER = "supplier";
    const CATEGORY_SUPPLIER = "category_supplier";
    const PURCHASE_ORDER = "purchase_order";

    const PURCHASE_ORDER_REPORT = "purchase_order_report";
    const PURCHASE_ORDER_PRODUCT_REPORT = "purchase_order_product_report";
    const PURCHASE_ORDER_PRODUCT_DETAIL_REPORT = "purchase_order_product_detail_report";

    const ALL = [
        self::SETTING,
        self::SUPPLIER,
        self::CATEGORY_SUPPLIER,
        self::PURCHASE_ORDER,

        self::PURCHASE_ORDER_REPORT,
        self::PURCHASE_ORDER_PRODUCT_REPORT,
        self::PURCHASE_ORDER_PRODUCT_DETAIL_REPORT,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::SUPPLIER => PermissionHelper::TYPE_ALL,
        self::CATEGORY_SUPPLIER => PermissionHelper::TYPE_ALL,
        self::PURCHASE_ORDER => PermissionHelper::TYPE_ALL,

        self::PURCHASE_ORDER_REPORT => [PermissionHelper::TYPE_READ],
        self::PURCHASE_ORDER_PRODUCT_REPORT => [PermissionHelper::TYPE_READ],
        self::PURCHASE_ORDER_PRODUCT_DETAIL_REPORT => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Pembelian",
        self::SUPPLIER => "Supplier",
        self::CATEGORY_SUPPLIER => "Kategori Supplier",
        self::PURCHASE_ORDER => "Pembelian",

        self::PURCHASE_ORDER_REPORT => "Laporan Pembelian",
        self::PURCHASE_ORDER_PRODUCT_REPORT => "Laporan Pembelian Barang",
        self::PURCHASE_ORDER_PRODUCT_DETAIL_REPORT => "Laporan Pembelian Barang Detail",
    ];
}
