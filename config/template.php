<?php

return [
    'title' => env('APP_NAME', 'Template Project'),
    'subtitle' => 'Sistem Logistik RSMH',

    'logo_auth' => 'files/images/logo_rsmh.png',
    'logo_auth_background' => 'white',

    'logo_panel' => 'files/images/logo_rsmh.png',
    'logo_panel_background' => 'white',

    'registration_route' => 'register',
    'registration_default_role' => 'Member',

    'forgot_password_route' => 'password.request',
    'reset_password_route' => 'password.reset',

    // 'email_verification_route' => 'verification.index',
    'email_verification_route' => '',
    'email_verification_delay_time' => 30,

    'email_verify_route' => 'verification.verify',

    'profile_route' => 'profile',
    'profile_image' => 'assets/media/avatars/profile.png',

    'menu' => [
        [
            'text' => 'Home',
            'route'  => 'dashboard.index',
            'icon' => 'ki-duotone ki-home',
        ],

        /*
        | ======================================
        | ============== LOGISTIC ==============
        | ======================================
        */
        [
            // 'id' => 'menu_admin'
            'header' => 'Logistik',
        ],
        [
            'text' => 'Permintaan',
            'route' => 'stock_request.index',
            'icon' => 'ki-duotone ki-arrow-right',
        ],
        [
            'text' => 'Pengeluaran',
            'route' => 'stock_expense.index',
            'icon' => 'ki-duotone ki-arrow-right',
        ],
        [
            'text' => 'Import Data - Pengeluaran',
            'route' => 'i_stock_expense.index',
            'icon' => 'ki-duotone ki-arrow-right',
        ],
        [
            // 'id' => 'menu_admin'
            'text' => 'Master Data',
            'icon' => 'ki-duotone ki-category',
            'submenu' => [
                [
                    'text' => 'Gudang',
                    'route'  => 'warehouse.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Produk',
                    'route'  => 'product.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Kategori Produk',
                    'route'  => 'category_product.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Satuan',
                    'route'  => 'unit.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
            ],
        ],
        [
            'id' => 'menu_r_logistic',
            'text' => 'Laporan Keseluruhan',
            'icon' => 'ki-duotone ki-chart-simple',
            'submenu' => [
                [
                    'text' => 'Stok Akhir',
                    'route'  => 'r_current_stock.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Stok Akhir Detail',
                    'route'  => 'r_current_stock_detail.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Kartu Stok',
                    'route'  => 'r_history_stock.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Kartu Stok Detail',
                    'route'  => 'r_history_stock_detail.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Pengeluaran',
                    'route'  => 'r_stock_expense.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'id' => 'submenu_r_stock_expired',
                    'text' => 'Stok Expired',
                    'route'  => 'r_stock_expired.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
            ],
        ],
        [
            'id' => 'menu_r_logistic_warehouse',
            'text' => 'Laporan Per Gudang',
            'icon' => 'ki-duotone ki-chart-simple',
            'submenu' => [
                [
                    'text' => 'Stok Akhir',
                    'route'  => 'r_current_stock_warehouse.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Stok Akhir Detail',
                    'route'  => 'r_current_stock_detail_warehouse.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Kartu Stok',
                    'route'  => 'r_history_stock_warehouse.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Kartu Stok Detail',
                    'route'  => 'r_history_stock_detail_warehouse.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Pengeluaran',
                    'route'  => 'r_stock_expense_warehouse.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'id' => 'submenu_r_stock_expired_warehouse',
                    'text' => 'Stok Expired',
                    'route'  => 'r_stock_expired_warehouse.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
            ],
        ],
        // [
        //     'text' => 'Pengaturan',
        //     'route'  => 'setting_logistic.index',
        //     'icon' => 'ki-duotone ki-setting-2',
        // ],

        /*
        | ======================================
        | ============= PURCHASING =============
        | ======================================
        */
        [
            // 'id' => 'menu_admin'
            'header' => 'Pembelian',
        ],
        [
            'text' => 'Pembelian',
            'route' => 'purchase_order.index',
            'icon' => 'ki-duotone ki-arrow-right',
        ],
        [
            'text' => 'Import Data - Pembelian',
            'route' => 'i_purchase_order.index',
            'icon' => 'ki-duotone ki-arrow-right',
        ],
        [
            // 'id' => 'menu_admin'
            'text' => 'Master Data',
            'icon' => 'ki-duotone ki-category',
            'submenu' => [
                [
                    'text' => 'Supplier',
                    'route' => 'supplier.index',
                ],
                [
                    'text' => 'Kategori Supplier',
                    'route' => 'category_supplier.index',
                ],
            ],
        ],
        [
            // 'id' => 'menu_admin'
            'text' => 'Laporan',
            'icon' => 'ki-duotone ki-chart-simple',
            'submenu' => [
                [
                    'text' => 'Pembelian',
                    'route'  => 'r_purchase_order.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Pembelian Produk',
                    'route'  => 'r_purchase_order_product.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
                [
                    'text' => 'Pembelian Produk Detail',
                    'route'  => 'r_purchase_order_product_detail.index',
                    'icon' => 'ki-duotone ki-element-11',
                ],
            ],
        ],
        // [
        //     'text' => 'Pengaturan',
        //     'route'  => 'setting_purchasing.index',
        //     'icon' => 'ki-duotone ki-setting-2',
        // ],

        /*
        | ======================================
        | ============== DOCUMENT ==============
        | ======================================
        */
        [
            'header' => 'Dokumen',
        ],
        [
            'id' => 'menu_approval',
            'text' => 'Persetujuan',
            'route' => 'approval.index',
            'icon' => 'ki-duotone ki-arrow-right',
        ],
        [
            // 'id' => 'menu_admin'
            'text' => 'Master Data',
            'icon' => 'ki-duotone ki-category',
            'submenu' => [
                [
                    'text' => 'Aturan Persetujuan',
                    'route' => 'approval_config.index',
                ],
                [
                    'text' => 'Status Persetujuan',
                    'route' => 'status_approval.index',
                ],
            ],
        ],

        /*
        | ======================================
        | ============= FINANCE =============
        | ======================================
        */
        [
            // 'id' => 'menu_admin'
            'header' => 'Keuangan',
        ],
        [
            // 'id' => 'menu_admin'
            'text' => 'Master Data',
            'icon' => 'ki-duotone ki-category',
            'submenu' => [
                [
                    'text' => 'Pajak',
                    'route' => 'tax.index',
                ],
                [
                    'text' => 'Table Editor - Pajak',
                    'route' => 'tax_editor.index',
                ],
            ],
        ],

        /*
        | ======================================
        | ========== GENERAL SETTING ===========
        | ======================================
        */

        [
            // 'id' => 'menu_admin'
            'header' => 'Sistem',
        ],
        [
            // 'id' => 'menu_admin'
            'text' => 'Pengaturan',
            'icon' => 'ki-duotone ki-setting-2',
            'submenu' => [
                // [
                //     'text' => 'Pengaturan Global',
                //     'route' => 'setting_core.index',
                // ],
                // [
                //     'text' => 'Perusahaan',
                //     'route' => 'company.index',
                // ],
                [
                    'text' => 'Pengguna',
                    'route' => 'user.index',
                ],
                [
                    'text' => 'Jabatan',
                    'route' => 'role.index',
                ],
                [
                    'text' => 'Akses',
                    'route' => 'permission.index',
                ],
                [
                    'text' => 'Import Data',
                    'route'  => 'import_data_logistic.index',
                    'icon' => 'ki-duotone ki-setting-2',
                ],
            ],
        ],
    ],
];
