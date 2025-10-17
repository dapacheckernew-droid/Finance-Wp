<?php

class FMTM_Bootstrap
{
    public static function init(): void
    {
        add_action('init', ['FMTM_Activator', 'ensure_capabilities']);
        FMTM_Tenant_Manager::init();
        FMTM_Admin_Menu::init();
        FMTM_Rest_Routes::init();
    }
}
