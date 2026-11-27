<?php
/**
 * Public website brief: /brief.php?token=... (link created in the CRM from a client).
 * GET shows the form (Spanish or English, ?lang=es|en); POST saves the answers as JSON (draft or submit).
 * Uses the CRM code (admin/app) without a CRM session — the secret token identifies the brief.
 * Questions: includes/brief-schema.php. Markup: admin/app/Views/brief/page.php + admin/assets/app/brief.js.
 */
declare(strict_types=1);

require __DIR__ . '/admin/app/bootstrap.php';

use Crm\Core\BriefForm;
use Crm\Core\Pdf;
use Crm\Core\Settings;
use Crm\Core\View;
use Crm\Models\Brief;

header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer'); // never leak the brief token to other sites
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow');

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$token = trim((string)($isPost ? ($_POST['token'] ?? '') : ($_GET['token'] ?? '')));
$brief = null;
$dbError = false;
try {
    $brief = Brief::findByToken($token);
    $s = Settings::all();
} catch (Throwable $e) {
    $dbError = true;
    $s = Settings::DEFAULTS;
}

if ($isPost) {
    header('Content-Type: application/json; charset=utf-8');
    $lang = ($_POST['lang'] ?? 'es') === 'en' ? 'en' : 'es';
    $t = fn(string $es, string $en): string => $lang === 'en' ? $en : $es;
    $reply = function (bool $ok, string $message, int $code = 200): never {
        http_response_code($code);
        echo json_encode(['success' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    };

    if (!empty($_POST['hp_url'])) {
        $reply(true, $t('Guardado.', 'Saved.')); // honeypot: a bot filled the hidden field
    }
    if ($dbError) {
        $reply(false, $t('No fue posible guardar. Intente de nuevo en unos minutos.', 'We could not save. Please try again in a few minutes.'), 503);
    }
    if (!$brief) {
        $reply(false, $t('Enlace no válido.', 'Invalid link.'), 404);
    }
    if ($brief['status'] === 'completado') {
        $reply(false, $t('Este formulario ya fue enviado. Si necesita corregir algo, contáctenos.', 'This form was already submitted. If you need to correct something, please contact us.'), 409);
    }

    $submit = ($_POST['action'] ?? 'draft') === 'submit';
    $schema = ninubo_brief_schema($lang, $brief['brief_profile']);
    $step = max(1, min(count($schema), (int)($_POST['current_step'] ?? 1)));
    $data = BriefForm::collect($schema, $_POST);

    if ($submit && ($missing = BriefForm::missingRequired($schema, $data))) {
        $reply(false, $t('Falta completar: ', 'Please complete: ') . implode(', ', $missing) . '.', 422);
    }

    try {
        Brief::saveFromClient($brief, $data, $step, $lang, $submit);
    } catch (Throwable $e) {
        $reply(false, $t('No fue posible guardar. Intente de nuevo en unos minutos.', 'We could not save. Please try again in a few minutes.'), 500);
    }

    if ($submit) {
        // Notify the team (same mail() caveat as the contact form).
        $to = $s['business_email'] ?: 'contacto@ninubo.com';
        $subject = mb_encode_mimeheader('[NINUBO CRM] Website brief submitted: ' . $brief['client_label'], 'UTF-8');
        $body = "\"{$brief['client_label']}\" submitted their website brief" . ($brief['package_name'] ? " ({$brief['package_name']})" : '') . ".\n\n"
            . 'View answers: ' . ninubo_absolute_url('admin/briefs/' . (int)$brief['id'], 'es') . "\n"
            . 'Form language: ' . ($lang === 'en' ? 'English' : 'Spanish') . "\n"
            . 'Date: ' . date('M j, Y g:i A') . "\n";
        @mail($to, $subject, $body, "From: NINUBO Web <no-reply@ninubo.com>\r\nContent-Type: text/plain; charset=UTF-8\r\n");
        $reply(true, $t('¡Gracias! Recibimos su información.', 'Thank you! We received your information.'));
    }
    $reply(true, $t('Guardado.', 'Saved.'));
}

// GET: the language of the link (?lang=) wins; otherwise the one the client last used / the client's preference.
$lang = in_array($_GET['lang'] ?? '', ['es', 'en'], true) ? $_GET['lang'] : (($brief['lang'] ?? 'es') === 'en' ? 'en' : 'es');
$schema = ninubo_brief_schema($lang, $brief['brief_profile'] ?? null);

if (!$brief) {
    http_response_code($dbError ? 503 : 404);
}

echo View::partial('brief/page', [
    'brief' => $brief,
    'dbError' => $dbError,
    'lang' => $lang,
    'schema' => $schema,
    'data' => $brief ? Brief::answersOrPrefill($brief, $schema) : [],
    's' => $s,
    'logo' => $dbError ? null : Pdf::logoDataUri(),
    'self' => ninubo_url('brief.php', 'es') . '?token=' . rawurlencode($token),
]);
