<?php

class FMTM_Activator
{
    public static function activate(): void
    {
        self::create_tables();
        self::register_roles();
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
        $capabilities = [
            'read' => true,
            'manage_fmtm_tenant' => true,
            'manage_fmtm_invoices' => true,
            'manage_fmtm_cash' => true,
        ];
        add_role('fmtm_owner', __('Tenant Owner', 'finance-mt'), $capabilities);

        add_role('fmtm_accountant', __('Accountant', 'finance-mt'), [
            'read' => true,
            'manage_fmtm_invoices' => true,
            'manage_fmtm_cash' => true,
        ]);

        add_role('fmtm_staff', __('Staff', 'finance-mt'), [
            'read' => true,
            'manage_fmtm_invoices' => true,
        ]);

        add_role('fmtm_viewer', __('Viewer', 'finance-mt'), [
            'read' => true,
        ]);
    }
}
