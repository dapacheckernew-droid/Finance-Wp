<?php

class FMTM_View_Renderer
{
    public static function render(string $view, array $data = []): string
    {
        $path = FMTM_PLUGIN_DIR . 'views/' . $view . '.php';
        if (!file_exists($path)) {
            return '';
        }
        ob_start();
        extract($data, EXTR_SKIP);
        include $path;
        return ob_get_clean();
    }
}
