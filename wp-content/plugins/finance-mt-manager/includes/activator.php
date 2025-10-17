<?php

class FMTM_Activator
{
    public static function activate(): void
    {
        self::create_tables();
        self::register_roles();
        self::ensure_capabilities();
    }

    public static function deactivate(): void
    {
        // Roles intentionally kept to avoid capability loss when reactivating.
    }

    private static function create_tables(): void
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $tenants_table = $wpdb->prefix . 'fmtm_tenants';
        $user_tenants_table = $wpdb->prefix . 'fmtm_user_tenants';

        $sql = "CREATE TABLE {$tenants_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            slug VARCHAR(191) NOT NULL UNIQUE,
            db_name VARCHAR(191) NOT NULL,
            db_user VARCHAR(191) NOT NULL,
            db_password VARCHAR(191) NOT NULL,
            db_host VARCHAR(191) NOT NULL DEFAULT 'localhost',
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        $sql2 = "CREATE TABLE {$user_tenants_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            tenant_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY tenant_user (tenant_id, user_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        dbDelta($sql2);
    }

    private static function register_roles(): void
    {
        $definitions = [
            'fmtm_owner' => [
                'label' => __('Tenant Owner', 'finance-mt'),
                'caps' => [
                    'read' => true,
                    'manage_fmtm_tenant' => true,
                    'manage_fmtm_invoices' => true,
                    'manage_fmtm_cash' => true,
                ],
            ],
            'fmtm_accountant' => [
                'label' => __('Accountant', 'finance-mt'),
                'caps' => [
                    'read' => true,
                    'manage_fmtm_invoices' => true,
                    'manage_fmtm_cash' => true,
                ],
            ],
            'fmtm_staff' => [
                'label' => __('Staff', 'finance-mt'),
                'caps' => [
                    'read' => true,
                    'manage_fmtm_invoices' => true,
                ],
            ],
            'fmtm_viewer' => [
                'label' => __('Viewer', 'finance-mt'),
                'caps' => [
                    'read' => true,
                ],
            ],
        ];

        foreach ($definitions as $role => $definition) {
            if (!get_role($role)) {
                add_role($role, $definition['label'], $definition['caps']);
                continue;
            }

            $existing = get_role($role);
            foreach ($definition['caps'] as $cap => $grant) {
                if ($grant) {
                    $existing->add_cap($cap);
                }
            }
        }
    }

    public static function ensure_capabilities(): void
    {
        $caps = [
            'manage_fmtm_tenant',
            'manage_fmtm_invoices',
            'manage_fmtm_cash',
        ];

        foreach (['administrator', 'editor'] as $role_name) {
            $role = get_role($role_name);
            if (!$role) {
                continue;
            }
            foreach ($caps as $cap) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap);
                }
            }
        }
    }
}
