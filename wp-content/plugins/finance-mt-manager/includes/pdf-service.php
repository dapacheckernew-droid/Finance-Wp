<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class FMTM_Pdf_Service
{
    public static function generate(string $html, string $filename)
    {
        if (!class_exists(Dompdf::class)) {
            return new WP_Error('pdf_library_missing', __('PDF library not installed', 'finance-mt'));
        }
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return new WP_REST_Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
