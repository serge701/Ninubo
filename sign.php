<?php
/**
 * Public page where the client reads and signs a contract: /sign.php?token=...
 * Records the e-signature evidence (consent, name, drawn signature, time, IP, device, document hash) in the CRM.
 * Uses the CRM code (admin/app) but no CRM session — the secret token in the link identifies the contract.
 */
declare(strict_types=1);

require __DIR__ . '/admin/app/bootstrap.php';

use Crm\Controllers\ContractController;
use Crm\Core\Database;
use Crm\Core\Pdf;
use Crm\Core\Settings;
use Crm\Core\View;
use Crm\Models\Activity;
use Crm\Models\Contract;

header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer'); // never leak the signing token to other sites
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow');

$token = (string)($_GET['token'] ?? '');
$k = $token !== '' ? Contract::findByToken($token) : null;
$s = Settings::all();
$self = 'sign.php?token=' . rawurlencode($token);
$errors = [];

if ($k && $k['status'] !== 'cancelled') {
    // Client downloads the PDF (the unsigned agreement, or the signed copy with its certificate).
    if (isset($_GET['pdf'])) {
        Contract::logEvent((int)$k['id'], 'pdf_downloaded', 'By the client from the signing page');
        ContractController::streamPdf($k, false);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $k['status'] === 'sent') {
        $name = trim((string)($_POST['signer_name'] ?? ''));
        $title = trim((string)($_POST['signer_title'] ?? ''));
        $email = trim((string)($_POST['signer_email'] ?? ''));
        $signature = Contract::cleanSignature((string)($_POST['signature'] ?? ''));

        if (empty($_POST['consent'])) {
            $errors[] = 'Please agree to use electronic records and signatures.';
        }
        if (mb_strlen($name) < 3) {
            $errors[] = 'Please type your full legal name.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!$signature) {
            $errors[] = 'Please draw your signature in the box.';
        }
        if (empty($_POST['agree'])) {
            $errors[] = 'Please confirm that you have read and agree to the agreement.';
        }
        // The client must be signing exactly the locked text shown on this page.
        if (!hash_equals((string)$k['body_hash'], (string)($_POST['doc_hash'] ?? ''))) {
            $errors[] = 'This agreement was updated. Please reload the page and review it again.';
        }

        if (!$errors) {
            $now = date('Y-m-d H:i:s');
            $updated = Database::run(
                "UPDATE contracts SET status = 'client_signed', client_consent_at = ?, client_signature = ?, client_signed_name = ?,
                    client_signed_title = ?, client_signed_email = ?, client_signed_at = ?, client_ip = ?, client_user_agent = ?
                 WHERE id = ? AND status = 'sent'",
                [$now, $signature, mb_substr($name, 0, 150), mb_substr($title, 0, 120), mb_substr($email, 0, 190), $now,
                 substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45), mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255), (int)$k['id']]
            )->rowCount();

            if ($updated === 1) {
                Contract::logEvent((int)$k['id'], 'consented', 'Electronic records and signature disclosure accepted');
                Contract::logEvent((int)$k['id'], 'client_signed', "Signed by {$name}" . ($title !== '' ? ", {$title}" : '') . ($email !== '' ? " <{$email}>" : '') . '; document SHA-256 ' . $k['body_hash']);
                Activity::log((int)$k['client_id'], null, 'Contract ' . $k['number'] . " signed electronically by {$name}.");

                // Notify the team (same mail() caveat as the contact form).
                $to = $s['business_email'] ?: 'contacto@ninubo.com';
                $subject = mb_encode_mimeheader('[NINUBO CRM] Contract ' . $k['number'] . ' signed by the client', 'UTF-8');
                $body = "{$name} signed contract {$k['number']} electronically on " . date('M j, Y g:i A') . ".\n\n"
                    . 'Countersign it here: ' . ninubo_absolute_url('admin/contracts/' . (int)$k['id'], 'es') . "\n";
                @mail($to, $subject, $body, "From: NINUBO Web <no-reply@ninubo.com>\r\nContent-Type: text/plain; charset=UTF-8\r\n");
            }
            header('Location: ' . $self . '&signed=1');
            exit;
        }
    }

    // Log views (at most once every 30 minutes per IP, so reloads don't flood the trail).
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['signed'])) {
        $recent = Database::value(
            "SELECT COUNT(*) FROM contract_events WHERE contract_id = ? AND event = 'viewed' AND ip = ? AND created_at > NOW() - INTERVAL 30 MINUTE",
            [(int)$k['id'], (string)($_SERVER['REMOTE_ADDR'] ?? '')]
        );
        if (!(int)$recent) {
            Contract::logEvent((int)$k['id'], 'viewed');
        }
    }
    $k = Contract::find((int)$k['id']);
}

if (!$k) {
    http_response_code(404);
}

echo View::partial('sign/page', [
    'k' => $k,
    's' => $s,
    'errors' => $errors,
    'post' => $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [],
    'self' => $self,
    'logo' => Pdf::logoDataUri(),
    'justSigned' => isset($_GET['signed']),
]);
