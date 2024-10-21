<?php

namespace App\Permissions;

class AccessFinance
{
    const TAX = "tax";

    const ALL = [
        self::TAX,
    ];

    const TYPE_ALL = [
        self::TAX => PermissionHelper::TYPE_ALL,
    ];

    const TRANSLATE = [
        self::TAX => "Pajak",
    ];
}