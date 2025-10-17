<?php

class FMTM_Admin_Menu
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'register']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function register(): void
    {
        add_menu_page(
            __('Finance MT', 'finance-mt'),
            __('Finance MT', 'finance-mt'),
            'manage_fmtm_invoices',
            'fmtm-dashboard',
            [self::class, 'render_dashboard'],
            'dashicons-chart-line'
        );

        add_submenu_page(
            'fmtm-dashboard',
            __('Invoices', 'finance-mt'),
            __('Invoices', 'finance-mt'),
            'manage_fmtm_invoices',
            'fmtm-invoices',
            [self::class, 'render_invoices']
        );

        add_submenu_page(
            'fmtm-dashboard',
            __('Cash Ledger', 'finance-mt'),
            __('Cash Ledger', 'finance-mt'),
            'manage_fmtm_cash',
            'fmtm-cash-ledger',
            [self::class, 'render_cash']
        );
    }

    public static function enqueue(string $hook): void
    {
        if (!str_contains($hook, 'fmtm')) {
            return;
        }
        wp_enqueue_style('fmtm-admin', FMTM_PLUGIN_URL . 'assets/admin.css', [], FMTM_PLUGIN_VERSION);
        wp_enqueue_script('fmtm-admin', FMTM_PLUGIN_URL . 'assets/admin.js', ['jquery'], FMTM_PLUGIN_VERSION, true);
    }

    public static function render_dashboard(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Access denied', 'finance-mt'));
        }
        $tenants = FMTM_Tenant_Manager::get_tenants();
        include FMTM_PLUGIN_DIR . 'views/admin-dashboard.php';
    }

    public static function render_invoices(): void
    {
        $tenant_slug = self::get_current_user_tenant();
        if (!$tenant_slug) {
            self::render_no_tenant();
            return;
        }
        $connection = FMTM_Tenant_Manager::get_tenant_connection($tenant_slug);
        $controller = new FMTM_Invoices_Controller($connection, $tenant_slug);
        $notice = $controller->handle_request();
        $invoices = $controller->list_invoices();
        include FMTM_PLUGIN_DIR . 'views/admin-invoices.php';
    }

    public static function render_cash(): void
    {
        $tenant_slug = self::get_current_user_tenant();
        if (!$tenant_slug) {
            self::render_no_tenant();
            return;
        }
        $connection = FMTM_Tenant_Manager::get_tenant_connection($tenant_slug);
        $controller = new FMTM_Cash_Controller($connection, $tenant_slug);
        $notice = $controller->handle_request();
        $entries = $controller->list_entries();
        include FMTM_PLUGIN_DIR . 'views/admin-cash.php';
    }

    private static function get_current_user_tenant(): ?string
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return null;
        }
        return get_user_meta($user_id, 'fmtm_default_tenant', true) ?: null;
    }

    private static function render_no_tenant(): void
    {
        echo '<div class="notice notice-warning"><p>' . esc_html__('No tenant assigned. Contact administrator.', 'finance-mt') . '</p></div>';
    }
}
