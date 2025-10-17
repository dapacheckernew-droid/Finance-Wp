<?php

class FMTM_Tenant_Manager
{
    public static function init(): void
    {
        add_action('admin_post_fmtm_create_tenant', [self::class, 'handle_create_tenant']);
    }

    public static function get_tenants(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fmtm_tenants';
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A) ?: [];
    }

    public static function get_tenant_by_slug(string $slug): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'fmtm_tenants';
        $tenant = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s", $slug), ARRAY_A);
        return $tenant ?: null;
    }

    public static function handle_create_tenant(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission.', 'finance-mt'));
        }

        check_admin_referer('fmtm_create_tenant');

        $name = sanitize_text_field($_POST['company_name'] ?? '');
        $slug = sanitize_title($_POST['company_slug'] ?? $name);
        $email = sanitize_email($_POST['admin_email'] ?? '');

        if (!$name || !$email) {
            wp_redirect(add_query_arg('fmtm_error', 'missing', wp_get_referer()));
            exit;
        }

        $tenant = self::create_tenant($name, $slug, $email);

        if (is_wp_error($tenant)) {
            wp_redirect(add_query_arg('fmtm_error', $tenant->get_error_code(), wp_get_referer()));
            exit;
        }

        wp_redirect(add_query_arg(['fmtm_created' => $tenant['slug']], wp_get_referer()));
        exit;
    }

    public static function create_tenant(string $name, string $slug, string $email)
    {
        global $wpdb;
        $slug = sanitize_title($slug);

        $tenants_table = $wpdb->prefix . 'fmtm_tenants';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$tenants_table} WHERE slug = %s", $slug));
        if ($exists) {
            return new WP_Error('tenant_exists', __('Tenant slug already exists.', 'finance-mt'));
        }

        $db_name = 'fmtm_' . preg_replace('/[^a-z0-9_]/', '', $slug);
        $db_user = DB_USER;
        $db_password = DB_PASSWORD;
        $db_host = DB_HOST;

        $created = self::create_database_if_not_exists($db_name);
        if (is_wp_error($created)) {
            return $created;
        }

        $migration = new FMTM_Tenant_Migrator($db_name, $db_user, $db_password, $db_host);
        $migration->run();

        $wpdb->insert($tenants_table, [
            'name' => $name,
            'slug' => $slug,
            'db_name' => $db_name,
            'db_user' => $db_user,
            'db_password' => $db_password,
            'db_host' => $db_host,
            'created_at' => current_time('mysql'),
        ]);

        $tenant_id = $wpdb->insert_id;

        $password = wp_generate_password(16, true, true);
        $user_id = username_exists($slug . '_owner');
        if (!$user_id) {
            $user_id = wp_create_user($slug . '_owner', $password, $email);
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $name . ' Owner',
                'role' => 'fmtm_owner',
            ]);
        }

        $user_tenants_table = $wpdb->prefix . 'fmtm_user_tenants';
        $wpdb->insert($user_tenants_table, [
            'user_id' => $user_id,
            'tenant_id' => $tenant_id,
            'role' => 'fmtm_owner',
            'created_at' => current_time('mysql'),
        ]);

        update_user_meta($user_id, 'fmtm_default_tenant', $slug);
        update_user_meta($user_id, 'fmtm_generated_password', $password);

        return [
            'id' => $tenant_id,
            'slug' => $slug,
            'password' => $password,
            'username' => $slug . '_owner',
        ];
    }

    public static function get_tenant_connection(string $slug): ?wpdb
    {
        $tenant = self::get_tenant_by_slug($slug);
        if (!$tenant) {
            return null;
        }

        $connection = new wpdb($tenant['db_user'], $tenant['db_password'], $tenant['db_name'], $tenant['db_host']);
        $connection->set_prefix('fmtm_');
        return $connection;
    }

    private static function create_database_if_not_exists(string $db_name)
    {
        $mysqli = mysqli_init();
        if (!$mysqli->real_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
            return new WP_Error('db_connect', __('Failed to connect to database server.', 'finance-mt'));
        }

        $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $db_name);
        $created = $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$created) {
            return new WP_Error('db_create', __('Unable to create tenant database.', 'finance-mt'));
        }
        $mysqli->close();
        return true;
    }
}
