<?php

namespace App\Permissions;

class AccessFinance
{
    const TAX = "tax";
    const TAX_EDITOR = "tax_editor";

    const ALL = [
        self::TAX,
        self::TAX_EDITOR,
    ];

    const TYPE_ALL = [
        self::TAX => PermissionHelper::TYPE_ALL,
        self::TAX_EDITOR => [PermissionHelper::TYPE_READ],
    ];

    const TRANSLATE = [
        self::TAX => "Pajak",
        self::TAX_EDITOR => "Table Editor - Pajak",
    ];
}
