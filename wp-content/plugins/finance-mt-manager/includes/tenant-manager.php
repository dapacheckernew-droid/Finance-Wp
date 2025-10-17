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

        $redirect = wp_get_referer() ?: admin_url('admin.php?page=fmtm-dashboard');

        if (!$name || !$email) {
            self::set_notice(['type' => 'error', 'message' => __('Company name and admin email are required.', 'finance-mt')]);
            wp_safe_redirect(add_query_arg('fmtm_notice', '1', $redirect));
            exit;
        }

        $tenant = self::create_tenant($name, $slug, $email);

        if (is_wp_error($tenant)) {
            self::set_notice(['type' => 'error', 'message' => $tenant->get_error_message()]);
            wp_safe_redirect(add_query_arg('fmtm_notice', '1', $redirect));
            exit;
        }

        $message = sprintf(
            /* translators: 1: username, 2: password */
            __('Tenant created. Owner login: %1$s / %2$s', 'finance-mt'),
            $tenant['username'],
            $tenant['password']
        );

        if (!empty($tenant['notice'])) {
            $message .= '\n' . $tenant['notice'];
        }

        self::set_notice(['type' => 'success', 'message' => $message]);

        wp_safe_redirect(add_query_arg(['fmtm_created' => $tenant['slug'], 'fmtm_notice' => '1'], $redirect));
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

        $db_user = DB_USER;
        $db_password = DB_PASSWORD;
        $db_host = DB_HOST;
        $db_name = 'fmtm_' . preg_replace('/[^a-z0-9_]/', '', $slug);
        $table_prefix = 'fmtm_';
        $storage_mode = 'dedicated';
        $fallback_notice = '';

        $created = self::create_database_if_not_exists($db_name);
        if (is_wp_error($created)) {
            $storage_mode = 'shared';
            $fallback_notice = sprintf(
                __('Dedicated database unavailable (%s). Created tenant inside main database instead.', 'finance-mt'),
                $created->get_error_message()
            );
            $db_name = DB_NAME;
            $table_prefix = self::generate_table_prefix($slug);
        }

        $connection = new wpdb($db_user, $db_password, $db_name, $db_host);
        $connection->set_prefix($table_prefix);
        $migration = new FMTM_Tenant_Migrator($connection, $table_prefix);
        $migrated = $migration->run();
        if (is_wp_error($migrated)) {
            return $migrated;
        }

        $inserted = $wpdb->insert($tenants_table, [
            'name' => $name,
            'slug' => $slug,
            'db_name' => $db_name,
            'db_user' => $db_user,
            'db_password' => $db_password,
            'db_host' => $db_host,
            'table_prefix' => $table_prefix,
            'storage_mode' => $storage_mode,
            'created_at' => current_time('mysql'),
        ]);

        if ($inserted === false) {
            return new WP_Error('tenant_store_failed', $wpdb->last_error ?: __('Failed to store tenant.', 'finance-mt'));
        }

        $tenant_id = $wpdb->insert_id;

        $password = wp_generate_password(16, true, true);
        $user_id = username_exists($slug . '_owner');
        if (!$user_id) {
            $user_id = wp_create_user($slug . '_owner', $password, $email);
            if (is_wp_error($user_id)) {
                return $user_id;
            }
        } else {
            $user_id = (int) $user_id;
            // ensure password for reused account when recreating tenants
            wp_set_password($password, $user_id);
        }

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $user_id = (int) $user_id;

        $updated = wp_update_user([
            'ID' => $user_id,
            'display_name' => $name . ' Owner',
            'role' => 'fmtm_owner',
        ]);
        if (is_wp_error($updated)) {
            return $updated;
        }

        $user_tenants_table = $wpdb->prefix . 'fmtm_user_tenants';
        $user_link = $wpdb->insert($user_tenants_table, [
            'user_id' => $user_id,
            'tenant_id' => $tenant_id,
            'role' => 'fmtm_owner',
            'created_at' => current_time('mysql'),
        ]);

        if ($user_link === false) {
            return new WP_Error('tenant_user_link_failed', $wpdb->last_error ?: __('Failed to link user to tenant.', 'finance-mt'));
        }

        update_user_meta($user_id, 'fmtm_default_tenant', $slug);
        update_user_meta($user_id, 'fmtm_generated_password', $password);

        return [
            'id' => $tenant_id,
            'slug' => $slug,
            'password' => $password,
            'username' => $slug . '_owner',
            'notice' => $fallback_notice,
        ];
    }

    public static function get_tenant_connection(string $slug): ?wpdb
    {
        $tenant = self::get_tenant_by_slug($slug);
        if (!$tenant) {
            return null;
        }

        $connection = new wpdb($tenant['db_user'], $tenant['db_password'], $tenant['db_name'], $tenant['db_host']);
        $table_prefix = $tenant['table_prefix'] ?? 'fmtm_';
        $connection->set_prefix($table_prefix);
        return $connection;
    }

    private static function create_database_if_not_exists(string $db_name)
    {
        $mysqli = mysqli_init();
        if (!$mysqli->real_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
            $error = mysqli_connect_error();
            return new WP_Error('db_connect', $error ?: __('Failed to connect to database server.', 'finance-mt'));
        }

        $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $db_name);
        $created = $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        if (!$created) {
            $error = $mysqli->error ?: __('Unable to create tenant database.', 'finance-mt');
            $mysqli->close();
            return new WP_Error('db_create', $error);
        }
        $mysqli->close();
        return true;
    }

    private static function generate_table_prefix(string $slug): string
    {
        $clean = preg_replace('/[^a-z0-9_]/', '', $slug);
        $base = rtrim('fmtm_' . substr($clean, 0, 20), '_');
        if ($base === 'fmtm') {
            $base .= '_' . strtolower(wp_generate_password(4, false));
        }

        $prefix = $base . '_';
        $suffix = 1;
        while (self::table_prefix_in_use($prefix)) {
            $prefix = $base . '_' . $suffix . '_';
            $suffix++;
        }

        return $prefix;
    }

    private static function table_prefix_in_use(string $prefix): bool
    {
        global $wpdb;
        $tenants_table = $wpdb->prefix . 'fmtm_tenants';
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$tenants_table} WHERE table_prefix = %s", $prefix));
    }

    private static function set_notice(array $notice): void
    {
        $key = self::notice_key();
        set_transient($key, $notice, MINUTE_IN_SECONDS * 5);
    }

    public static function consume_notice(): ?array
    {
        $key = self::notice_key();
        $notice = get_transient($key);
        if ($notice) {
            delete_transient($key);
        }
        return $notice ?: null;
    }

    private static function notice_key(): string
    {
        return 'fmtm_notice_' . get_current_user_id();
    }
}
