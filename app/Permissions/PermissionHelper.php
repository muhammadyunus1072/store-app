<?php

namespace App\Permissions;

use Illuminate\Support\Facades\Auth;
use App\Repositories\Core\User\UserRepository;

class PermissionHelper
{
    const SEPARATOR =  ".";

    const TYPE_CREATE = "create";
    const TYPE_READ = "read";
    const TYPE_UPDATE = "update";
    const TYPE_DELETE = "delete";
    const TYPE_ALL = [self::TYPE_CREATE, self::TYPE_READ, self::TYPE_UPDATE, self::TYPE_DELETE];
    const TRANSLATE_TYPE = [
        self::TYPE_CREATE => "Buat",
        self::TYPE_READ => "Lihat",
        self::TYPE_UPDATE => "Edit",
        self::TYPE_DELETE => "Hapus",
    ];

    const ROUTE_TYPE_CREATE = ['create', 'store'];
    const ROUTE_TYPE_READ = ['index', 'show', 'print', 'export', 'find'];
    const ROUTE_TYPE_UPDATE = ['edit', 'update'];
    const ROUTE_TYPE_DELETE = ['destroy'];

    const ACCESS_ALIASES = [
        'AccessCore' => AccessCore::class,
        'AccessDocument' => AccessDocument::class,
        'AccessFinance' => AccessFinance::class,
        'AccessLogistic' => AccessLogistic::class,
        'AccessPurchasing' => AccessPurchasing::class,
    ];

    const ACCESS_GROUPS = [
        'Utama' => AccessCore::ALL,
        'Dokumen' => AccessDocument::ALL,
        'Keuangan' => AccessFinance::ALL,
        'Logistik' => AccessLogistic::ALL,
        'Pembelian' => AccessPurchasing::ALL,
    ];

    const ACCESS_ALL = [
        ...AccessCore::ALL,
        ...AccessDocument::ALL,
        ...AccessFinance::ALL,
        ...AccessLogistic::ALL,
        ...AccessPurchasing::ALL,
    ];

    const ACCESS_TYPE_ALL = [
        ...AccessCore::TYPE_ALL,
        ...AccessDocument::TYPE_ALL,
        ...AccessFinance::TYPE_ALL,
        ...AccessLogistic::TYPE_ALL,
        ...AccessPurchasing::TYPE_ALL,
    ];

    const ACCESS_TRANSLATE = [
        ...AccessCore::TRANSLATE,
        ...AccessDocument::TRANSLATE,
        ...AccessFinance::TRANSLATE,
        ...AccessLogistic::TRANSLATE,
        ...AccessPurchasing::TRANSLATE,
    ];

    /*
    | Parameters
    | permission (string) : merupakan nama dari permission
    */
    public static function translate($permission)
    {
        $explode = explode(self::SEPARATOR, $permission);
        $access = $explode[0];
        $type = $explode[1];

        $translateAccess = isset(self::ACCESS_TRANSLATE[$access]) ? self::ACCESS_TRANSLATE[$access] : $access;
        $translateType = isset(self::TRANSLATE_TYPE[$type]) ? self::TRANSLATE_TYPE[$type] : $type;

        return $translateAccess . " - " . $translateType;
    }

    /*
    | Parameters
    | access (string) : merupakan access yang tersedia pada helper ini
    | type (string) : merupakan type yang tersedia pada helper ini
    */
    public static function transform($access, $type)
    {
        return $access . self::SEPARATOR . $type;
    }

    /*
    | Parameters
    | permission (string) : merupakan nama dari permission
    */
    public static function getAccess($permission)
    {
        return explode(self::SEPARATOR, $permission)[0];
    }


    /*
    | Parameters
    | permission (string) : merupakan nama dari permission
    */
    public static function getTranslatedAccess($permission)
    {
        return self::ACCESS_TRANSLATE[self::getAccess($permission)];
    }


    /*
    | Parameters
    | permission (string) : merupakan nama dari permission
    */
    public static function getType($permission)
    {
        return explode(self::SEPARATOR, $permission)[1];
    }

    /*
    | Parameters
    | permission (string) : merupakan nama dari permission
    */
    public static function getTranslatedType($permission)
    {
        return self::TRANSLATE_TYPE[self::getType($permission)];
    }

    /*
    | Parameters
    | route_name (string) : Nama Route
    */
    public static function isRoutePermitted($route_name, $user = null)
    {
        // Identifikasi Route
        $exploded_route_names = explode(".", $route_name);
        $access = $exploded_route_names[0];
        $route_type = $exploded_route_names[1];

        if (in_array($route_type, self::ROUTE_TYPE_CREATE)) {
            $type = self::TYPE_CREATE;
        } else if (in_array($route_type, self::ROUTE_TYPE_READ)) {
            $type = self::TYPE_READ;
        } else if (in_array($route_type, self::ROUTE_TYPE_UPDATE)) {
            $type = self::TYPE_UPDATE;
        } else {
            $type = self::TYPE_DELETE;
        }

        // Pemeriksaan Hak Akses
        $user = $user == null ? UserRepository::find(Auth::id()) : $user;
        return $user->hasPermissionTo(self::transform($access, $type));
    }
}
