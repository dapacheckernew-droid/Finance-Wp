<?php

class FMTM_Bootstrap
{
    public static function init(): void
    {
        FMTM_Tenant_Manager::init();
        FMTM_Admin_Menu::init();
        FMTM_Rest_Routes::init();
    }
}
