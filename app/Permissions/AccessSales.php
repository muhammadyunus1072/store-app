<?php

namespace App\Permissions;

class AccessSales
{
    const CASHIER_TRANSACTION = "cashier_transaction";
    const PAYMENT_METHOD = "payment_method";

    const ALL = [
        self::CASHIER_TRANSACTION,
        self::PAYMENT_METHOD,
    ];

    const TYPE_ALL = [
        self::CASHIER_TRANSACTION => PermissionHelper::TYPE_ALL,
        self::PAYMENT_METHOD => PermissionHelper::TYPE_ALL,
    ];

    const TRANSLATE = [
        self::CASHIER_TRANSACTION => "Transaksi Kasir",
        self::PAYMENT_METHOD => "Metode Pembayaran",
    ];
}
