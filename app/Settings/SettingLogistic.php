<?php

namespace App\Settings;

use App\Helpers\Logistic\Stock\StockHandler;
use App\Repositories\Core\Setting\SettingRepository;

class SettingLogistic
{
    const NAME = "logistic";

    const INFO_PRODUCT_CODE = "info_product_code";
    const INFO_PRODUCT_BATCH = "info_product_batch";
    const INFO_PRODUCT_EXPIRED_DATE = "info_product_expired_date";
    const INFO_PRODUCT_ATTACHMENT = "info_product_attachment";

    const SUBSTRACT_STOCK_METHOD = "substract_stock_method";
    const PRICE_INTEGER_VALUE = "price_integer_value";

    const APPROVAL_KEY_STOCK_REQUEST = "approval_key_stock_request";
    const APPROVAL_KEY_STOCK_EXPENSE = "approval_key_stock_expense";

    const ALL = [
        self::INFO_PRODUCT_CODE => true,
        self::INFO_PRODUCT_BATCH  => true,
        self::INFO_PRODUCT_EXPIRED_DATE => true,
        self::INFO_PRODUCT_ATTACHMENT => true,
        
        self::SUBSTRACT_STOCK_METHOD => StockHandler::SUBSTRACT_STOCK_METHOD_FIFO,
        self::PRICE_INTEGER_VALUE => true,

        self::APPROVAL_KEY_STOCK_REQUEST => "",
        self::APPROVAL_KEY_STOCK_EXPENSE => "",
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
