<?php

namespace App\Permissions;

class AccessDocument
{
    const APPROVAL = "approval";
    const APPROVAL_STATUS = "approval_status";
    const APPROVAL_CONFIG = "approval_config";
    const STATUS_APPROVAL = "status_approval";

    const ALL = [
        self::APPROVAL,
        self::APPROVAL_CONFIG,
        self::STATUS_APPROVAL,
    ];

    const TYPE_ALL = [
        self::APPROVAL => [PermissionHelper::TYPE_READ],
        self::APPROVAL_STATUS => [PermissionHelper::TYPE_CREATE, PermissionHelper::TYPE_DELETE],
        self::APPROVAL_CONFIG => PermissionHelper::TYPE_ALL,
        self::STATUS_APPROVAL => PermissionHelper::TYPE_ALL,
    ];

    const TRANSLATE = [
        self::APPROVAL => "Persetujuan",
        self::APPROVAL_STATUS => "Persetujuan - Status",
        self::APPROVAL_CONFIG => "Aturan Persetujuan",
        self::STATUS_APPROVAL => "Status Persetujuan",
    ];
}
