<?php

namespace App\Helpers;

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

    // CORE
    const ACCESS_DASHBOARD = "dashboard";
    const ACCESS_USER = "user";
    const ACCESS_PERMISSION = "permission";
    const ACCESS_ROLE = "role";
    const ACCESS_COMPANY = "company";

    // LOGISTIC
    const ACCESS_UNIT = "unit";
    const ACCESS_CATEGORY_PRODUCT = "category_product";
    const ACCESS_PRODUCT = "product";
    const ACCESS_WAREHOUSE = "warehouse";
    const ACCESS_GOOD_RECEIVE = "good_receive";
    const ACCESS_SETTING_LOGISTIC = "setting_logistic";
    const ACCESS_STOCK_REQUEST = "stock_request";
    const ACCESS_STOCK_EXPENSE = "stock_expense";

    // SALES
    const ACCESS_CATEGORY_CUSTOMER = "category_customer";
    const ACCESS_CUSTOMER = "customer";

    // FINANCE
    const ACCESS_TAX = "tax";

    // PURCHASING
    const ACCESS_CATEGORY_SUPPLIER = "category_supplier";
    const ACCESS_SUPPLIER = "supplier";
    const ACCESS_PURCHASE_REQUEST = "purchase_request";
    const ACCESS_PURCHASE_ORDER = "purchase_order";

    // DOCUMENT
    const ACCESS_APPROVAL = "approval";
    const ACCESS_APPROVAL_CONFIG = "approval_config";
    const ACCESS_STATUS_APPROVAL = "status_approval";

    const ACCESS_ALL = [
        // CORE
        self::ACCESS_DASHBOARD,
        self::ACCESS_USER,
        self::ACCESS_PERMISSION,
        self::ACCESS_ROLE,
        self::ACCESS_COMPANY,

        // LOGISTIC
        self::ACCESS_UNIT,
        self::ACCESS_CATEGORY_PRODUCT,
        self::ACCESS_PRODUCT,
        self::ACCESS_WAREHOUSE,
        self::ACCESS_GOOD_RECEIVE,
        self::ACCESS_SETTING_LOGISTIC,
        self::ACCESS_STOCK_REQUEST,
        self::ACCESS_STOCK_EXPENSE,

        // FINANCE
        self::ACCESS_TAX,

        // SALES
        self::ACCESS_CATEGORY_CUSTOMER,
        self::ACCESS_CUSTOMER,

        // PURCHASING
        self::ACCESS_CATEGORY_SUPPLIER,
        self::ACCESS_SUPPLIER,
        self::ACCESS_PURCHASE_REQUEST,
        self::ACCESS_PURCHASE_ORDER,

        // DOCUMENT
        self::ACCESS_APPROVAL,
        self::ACCESS_APPROVAL_CONFIG,
        self::ACCESS_STATUS_APPROVAL,
    ];

    const ACCESS_TYPE_ALL = [
        // CORE
        PermissionHelper::ACCESS_DASHBOARD => [PermissionHelper::TYPE_READ],
        PermissionHelper::ACCESS_USER => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_ROLE => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_PERMISSION => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_COMPANY => PermissionHelper::TYPE_ALL,

        // LOGISTIC
        PermissionHelper::ACCESS_UNIT => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_CATEGORY_PRODUCT => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_PRODUCT => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_WAREHOUSE => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_GOOD_RECEIVE => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_SETTING_LOGISTIC => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_STOCK_REQUEST => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_STOCK_EXPENSE => PermissionHelper::TYPE_ALL,

        // FINANCE
        PermissionHelper::ACCESS_TAX => PermissionHelper::TYPE_ALL,

        // SALES
        PermissionHelper::ACCESS_CATEGORY_CUSTOMER => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_CUSTOMER => PermissionHelper::TYPE_ALL,

        // PURCHASING
        PermissionHelper::ACCESS_CATEGORY_SUPPLIER => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_SUPPLIER => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_PURCHASE_REQUEST => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_PURCHASE_ORDER => PermissionHelper::TYPE_ALL,

        // DOCUMENT
        PermissionHelper::ACCESS_APPROVAL => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_APPROVAL_CONFIG => PermissionHelper::TYPE_ALL,
        PermissionHelper::ACCESS_STATUS_APPROVAL => PermissionHelper::TYPE_ALL,
    ];

    const TRANSLATE_ACCESS = [
        // CORE
        self::ACCESS_DASHBOARD => "Dashboard",
        self::ACCESS_USER => "Pengguna",
        self::ACCESS_PERMISSION => "Akses",
        self::ACCESS_ROLE => "Jabatan",
        self::ACCESS_COMPANY => "Perusahaan",

        // LOGISTIC
        self::ACCESS_UNIT => "Satuan",
        self::ACCESS_CATEGORY_PRODUCT => "Kategori Produk",
        self::ACCESS_PRODUCT => "Produk",
        self::ACCESS_WAREHOUSE => "Gudang",
        self::ACCESS_GOOD_RECEIVE => "Penerimaan Barang",
        self::ACCESS_SETTING_LOGISTIC => "Pengaturan Logistic",
        self::ACCESS_STOCK_REQUEST => "Permintaan Barang",
        self::ACCESS_STOCK_EXPENSE => "Pengeluaran Barang",

        // FINANCE
        self::ACCESS_TAX => "Pajak",

        // SALES
        self::ACCESS_CATEGORY_CUSTOMER => "Kategori Customer",
        self::ACCESS_CUSTOMER => "Customer",

        // PURCHASING
        self::ACCESS_CATEGORY_SUPPLIER => "Kategori Supplier",
        self::ACCESS_SUPPLIER => "Supplier",
        self::ACCESS_PURCHASE_REQUEST => "Permintaan Pembelian",
        self::ACCESS_PURCHASE_ORDER => "Pembelian",

        // DOCUMENT
        self::ACCESS_APPROVAL => "Persetujuan",
        self::ACCESS_APPROVAL_CONFIG => "Aturan Persetujuan",
        self::ACCESS_STATUS_APPROVAL => "Status Persetujuan",
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

        $translateAccess = isset(self::TRANSLATE_ACCESS[$access]) ? self::TRANSLATE_ACCESS[$access] : $access;
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
        return self::TRANSLATE_ACCESS[self::getAccess($permission)];
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
