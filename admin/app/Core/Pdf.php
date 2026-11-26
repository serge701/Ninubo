<?php
declare(strict_types=1);

namespace Crm\Core;

use Dompdf\Dompdf;
use Dompdf\Options;

/** Renders a view to PDF with Dompdf (admin/vendor, installed with Composer and committed). */
final class Pdf
{
    /** Streams the PDF to the browser as a download (or inline preview with $inline). */
    public static function stream(string $view, array $data, string $filename, bool $inline = false): never
    {
        $pdf = self::render($view, $data);
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . str_replace('"', '', $filename) . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, no-store');
        echo $pdf;
        exit;
    }

    public static function render(string $view, array $data): string
    {
        require_once CRM_ROOT . '/vendor/autoload.php';

        $options = new Options();
        $options->set('isRemoteEnabled', false);      // never fetch URLs from document content
        $options->set('isPhpEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');  // bundled font with full UTF-8 (ñ, á, –, …)
        $options->setChroot([CRM_ROOT]);
        @mkdir(CRM_ROOT . '/storage/cache/dompdf', 0775, true);
        $options->set('fontCache', CRM_ROOT . '/storage/cache/dompdf');
        $options->set('tempDir', CRM_ROOT . '/storage/cache/dompdf');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(View::partial($view, $data), 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        return (string)$dompdf->output();
    }

    /** Business logo as a data: URI for documents (null when none is uploaded). */
    public static function logoDataUri(): ?string
    {
        $file = Settings::get('business_logo');
        $path = CRM_ROOT . '/storage/uploads/' . basename($file);
        if ($file === '' || !is_file($path)) {
            return null;
        }
        $mime = (string)(new \finfo(FILEINFO_MIME_TYPE))->file($path);
        return 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($path));
    }
}
