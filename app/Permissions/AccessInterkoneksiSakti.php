<?php

namespace App\Permissions;

class AccessInterkoneksiSakti
{
    const INTERKONEKSI_SAKTI_KBKI = "interkoneksi_sakti_kbki";
    const INTERKONEKSI_SAKTI_COA = "interkoneksi_sakti_coa";
    const INTERKONEKSI_SAKTI_SETTING = "interkoneksi_sakti_setting";

    const ALL = [
        self::INTERKONEKSI_SAKTI_KBKI,
        self::INTERKONEKSI_SAKTI_COA,
        self::INTERKONEKSI_SAKTI_SETTING,
    ];

    const TYPE_ALL = [
        self::INTERKONEKSI_SAKTI_KBKI => PermissionHelper::TYPE_ALL,
        self::INTERKONEKSI_SAKTI_COA => PermissionHelper::TYPE_ALL,
        self::INTERKONEKSI_SAKTI_SETTING => [PermissionHelper::TYPE_READ, PermissionHelper::TYPE_UPDATE],
    ];

    const TRANSLATE = [
        self::INTERKONEKSI_SAKTI_KBKI => "Interkoneksi Sakti KBKI",
        self::INTERKONEKSI_SAKTI_COA => "Interkoneksi Sakti COA",
        self::INTERKONEKSI_SAKTI_SETTING => "Interkoneksi Sakti Setting",
    ];
}
