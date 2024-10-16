<?php

namespace App\Settings;

use App\Helpers\Logistic\StockHelper;

class SettingLogistic
{
    const NAME = "logistic";

    const INPUT_PRODUCT_CODE = "input_product_code";
    const INPUT_PRODUCT_BATCH = "input_product_batch";
    const INPUT_PRODUCT_EXPIRED_DATE = "input_product_expired_date";
    const INPUT_PRODUCT_ATTACHMENT = "input_product_attachment";
    const SUBSTRACT_STOCK_METHOD = "substract_stock_method";
    const PRICE_INTEGER_VALUE = "price_integer_value";
    const APPROVAL_KEY_GOOD_RECEIVE = "approval_key_good_receive";
    const APPROVAL_KEY_STOCK_REQUEST = "approval_key_stock_request";
    const APPROVAL_KEY_STOCK_EXPENSE = "approval_key_stock_expense";
    const TAX_PPN_GOOD_RECEIVE_ID = "tax_ppn_good_receive_id";
    const TAX_PPN_INCLUDE_IN_STOCK_VALUE = "tax_ppn_include_in_stock_value";

    const ALL = [
        self::INPUT_PRODUCT_CODE => false,
        self::INPUT_PRODUCT_BATCH  => false,
        self::INPUT_PRODUCT_EXPIRED_DATE => false,
        self::INPUT_PRODUCT_ATTACHMENT => false,
        self::SUBSTRACT_STOCK_METHOD => StockHelper::SUBSTRACT_STOCK_METHOD_FIFO,
        self::PRICE_INTEGER_VALUE => true,
        self::APPROVAL_KEY_GOOD_RECEIVE => "",
        self::APPROVAL_KEY_STOCK_REQUEST => "",
        self::APPROVAL_KEY_STOCK_EXPENSE => "",
        self::TAX_PPN_GOOD_RECEIVE_ID => 1,
        self::TAX_PPN_INCLUDE_IN_STOCK_VALUE  => true,
    ];
}
