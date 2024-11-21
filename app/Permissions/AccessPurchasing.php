<?php

namespace App\Permissions;

class AccessPurchasing
{
    const SETTING = "setting_purchasing";
    const SUPPLIER = "supplier";
    const CATEGORY_SUPPLIER = "category_supplier";
    const PURCHASE_ORDER = "purchase_order";

    const R_PURCHASE_ORDER = "r_purchase_order";
    const R_PURCHASE_ORDER_PRODUCT = "r_purchase_order_product";
    const R_PURCHASE_ORDER_PRODUCT_DETAIL = "r_purchase_order_product_detail";

    const ALL = [
        self::SETTING,
        self::SUPPLIER,
        self::CATEGORY_SUPPLIER,
        self::PURCHASE_ORDER,

        self::R_PURCHASE_ORDER,
        self::R_PURCHASE_ORDER_PRODUCT,
        self::R_PURCHASE_ORDER_PRODUCT_DETAIL,
    ];

    const TYPE_ALL = [
        self::SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
        self::SUPPLIER => PermissionHelper::TYPE_ALL,
        self::CATEGORY_SUPPLIER => PermissionHelper::TYPE_ALL,
        self::PURCHASE_ORDER => PermissionHelper::TYPE_ALL,

        self::R_PURCHASE_ORDER => [PermissionHelper::TYPE_READ],
        self::R_PURCHASE_ORDER_PRODUCT => [PermissionHelper::TYPE_READ],
        self::R_PURCHASE_ORDER_PRODUCT_DETAIL => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::SETTING => "Pengaturan Pembelian",
        self::SUPPLIER => "Supplier",
        self::CATEGORY_SUPPLIER => "Kategori Supplier",
        self::PURCHASE_ORDER => "Pembelian",

        self::R_PURCHASE_ORDER => "Laporan - Pembelian",
        self::R_PURCHASE_ORDER_PRODUCT => "Laporan - Pembelian Barang",
        self::R_PURCHASE_ORDER_PRODUCT_DETAIL => "Laporan - Pembelian Barang Detail",
    ];
}
