<?php

namespace App\Settings;

use App\Repositories\Core\Setting\SettingRepository;

class SettingPurchasing
{
    const NAME = "purchasing";

    const PURCHASE_ORDER_PRODUCT_CODE = "purchase_order_product_code";
    const PURCHASE_ORDER_PRODUCT_BATCH = "purchase_order_product_batch";
    const PURCHASE_ORDER_PRODUCT_EXPIRED_DATE = "purchase_order_product_expired_date";
    const PURCHASE_ORDER_PRODUCT_ATTACHMENT = "purchase_order_product_attachment";

    const PURCHASE_ORDER_ADD_STOCK = "purchase_order_add_stock";
    const PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN = "purchase_order_add_stock_value_include_tax_ppn";

    const TAX_PPN_ID = "tax_ppn_id";

    const APPROVAL_KEY_PURCHASE_ORDER = "approval_key_purchase_order";

    const ALL = [
        self::PURCHASE_ORDER_PRODUCT_CODE => true,
        self::PURCHASE_ORDER_PRODUCT_BATCH => true,
        self::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE => true,
        self::PURCHASE_ORDER_PRODUCT_ATTACHMENT => true,
        self::PURCHASE_ORDER_ADD_STOCK => true,
        self::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN  => true,
        self::TAX_PPN_ID => 1,
        self::APPROVAL_KEY_PURCHASE_ORDER => "",
    ];

    public $parsedSetting;

    public function __construct()
    {
        $setting = SettingRepository::findBy(whereClause: [['name', self::NAME]]);
        $this->parsedSetting = json_decode($setting->setting, true);
    }

    public static function get($key)
    {
        $setting = app(self::class);

        if (!isset($setting->parsedSetting[$key])) {
            return null;
        }

        return $setting->parsedSetting[$key];
    }
}
