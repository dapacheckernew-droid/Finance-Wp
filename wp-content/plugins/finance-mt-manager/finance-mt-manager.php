<?php
/**
 * Plugin Name: Finance MT Manager
 * Description: Multi-tenant financial management plugin providing tenant onboarding, invoicing, cash ledger, and REST APIs.
 * Version: 0.1.0
 * Author: Finance MT Team
 * Requires PHP: 8.1
 * Requires at least: 6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FMTM_PLUGIN_VERSION', '0.1.0');
define('FMTM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FMTM_PLUGIN_URL', plugin_dir_url(__FILE__));

autoload_fmtm();

register_activation_hook(__FILE__, ['FMTM_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['FMTM_Activator', 'deactivate']);

// Ensure capabilities are available as soon as the plugin loads so menus render reliably.
add_action('plugins_loaded', ['FMTM_Activator', 'ensure_capabilities'], 0);

add_action('plugins_loaded', function () {
    load_plugin_textdomain('finance-mt', false, basename(dirname(__FILE__)) . '/languages');
    FMTM_Bootstrap::init();
});

function autoload_fmtm(): void
{
    spl_autoload_register(function ($class) {
        if (str_starts_with($class, 'FMTM_')) {
            $file = FMTM_PLUGIN_DIR . 'includes/' . strtolower(str_replace(['FMTM_', '_'], ['', '-'], $class)) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
            $parts = explode('_', $class);
            array_shift($parts);
            $path = FMTM_PLUGIN_DIR . 'includes/' . implode('/', array_map(fn($part) => strtolower($part), $parts)) . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
    });
}
