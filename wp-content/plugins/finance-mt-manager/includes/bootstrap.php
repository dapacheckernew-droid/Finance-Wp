<?php

class FMTM_Bootstrap
{
    public static function init(): void
    {
        add_action('init', ['FMTM_Activator', 'ensure_capabilities']);
        add_action('admin_init', ['FMTM_Activator', 'ensure_capabilities']);
        add_filter('user_has_cap', [self::class, 'grant_admin_fallback_caps'], 10, 4);
        FMTM_Tenant_Manager::init();
        FMTM_Admin_Menu::init();
        FMTM_Rest_Routes::init();
    }

    public static function grant_admin_fallback_caps(array $allcaps, array $caps, array $args, WP_User $user): array
    {
        if (empty($args[0])) {
            return $allcaps;
        }

        $target_caps = [
            'manage_fmtm_tenant',
            'manage_fmtm_invoices',
            'manage_fmtm_cash',
        ];

        if (in_array($args[0], $target_caps, true) && !empty($allcaps['manage_options'])) {
            $allcaps[$args[0]] = true;
        }

        return $allcaps;
    }
}
