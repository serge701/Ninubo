<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Session;
use Crm\Core\Settings;

final class SettingsController extends Controller
{
    private const LOGO_TYPES = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
    private const LOGO_MAX_BYTES = 2 * 1024 * 1024;

    public function show(): void
    {
        $this->view('settings/index', ['title' => 'Business settings', 's' => Settings::all()]);
    }

    public function update(): void
    {
        $values = [];
        foreach (array_keys(Settings::DEFAULTS) as $key) {
            if ($key !== 'business_logo' && array_key_exists($key, $_POST)) {
                $values[$key] = trim((string)$_POST[$key]);
            }
        }

        $errors = [];
        if (($values['business_name'] ?? '') === '') {
            $errors['business_name'] = 'Enter the name shown on documents.';
        }
        if (($values['business_email'] ?? '') !== '' && !filter_var($values['business_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['business_email'] = 'Enter a valid email address.';
        }
        foreach (['sales_tax_rate' => [0, 20], 'sales_tax_taxable_pct' => [0, 100]] as $key => [$min, $max]) {
            $v = $values[$key] ?? '';
            if (!is_numeric($v) || (float)$v < $min || (float)$v > $max) {
                $errors[$key] = "Enter a number between {$min} and {$max}.";
            }
        }
        foreach (['invoice_due_days', 'invoice_next_number', 'contract_next_number'] as $key) {
            if (!ctype_digit($values[$key] ?? '')) {
                $errors[$key] = 'Enter a whole number.';
            }
        }

        $logo = $_FILES['business_logo'] ?? null;
        $newLogo = null;
        if ($logo && ($logo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $type = $logo['error'] === UPLOAD_ERR_OK ? (string)(new \finfo(FILEINFO_MIME_TYPE))->file($logo['tmp_name']) : '';
            if ($logo['error'] !== UPLOAD_ERR_OK || !isset(self::LOGO_TYPES[$type]) || $logo['size'] > self::LOGO_MAX_BYTES) {
                $errors['business_logo'] = 'Upload a PNG, JPG or WEBP image up to 2 MB.';
            } else {
                $newLogo = 'logo-' . bin2hex(random_bytes(6)) . '.' . self::LOGO_TYPES[$type];
                if (!move_uploaded_file($logo['tmp_name'], self::uploadDir() . '/' . $newLogo)) {
                    $errors['business_logo'] = 'The logo could not be saved.';
                    $newLogo = null;
                }
            }
        }

        if ($errors) {
            if ($newLogo) {
                @unlink(self::uploadDir() . '/' . $newLogo);
            }
            $this->back('/settings', $errors, $values);
        }

        if ($newLogo) {
            $this->removeLogoFile();
            $values['business_logo'] = $newLogo;
        }
        Settings::save($values);
        $this->success('Settings saved.');
        $this->redirect('/settings');
    }

    public function deleteLogo(): void
    {
        $this->removeLogoFile();
        Settings::save(['business_logo' => '']);
        $this->success('Logo removed.');
        $this->redirect('/settings');
    }

    /** Streams the uploaded business logo (storage/ is not web-accessible). */
    public function logo(): void
    {
        $file = Settings::get('business_logo');
        $path = self::uploadDir() . '/' . basename($file);
        if ($file === '' || !is_file($path)) {
            $this->notFound();
        }
        $mime = (string)(new \finfo(FILEINFO_MIME_TYPE))->file($path);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=86400');
        readfile($path);
        exit;
    }

    public static function uploadDir(): string
    {
        $dir = CRM_ROOT . '/storage/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private function removeLogoFile(): void
    {
        $old = Settings::get('business_logo');
        if ($old !== '') {
            @unlink(self::uploadDir() . '/' . basename($old));
        }
    }
}
