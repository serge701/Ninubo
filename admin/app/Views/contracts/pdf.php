<?php
/**
 * Contract PDF: agreement text, signature blocks and — when signed electronically — a certificate page with the
 * audit trail (evidence of the e-signature).
 * @var array $k @var string $body @var array $events @var array $s @var ?string $logo
 */
use Crm\Models\Client;
use Crm\Models\Contract;

$watermark = ['draft' => 'DRAFT', 'cancelled' => 'CANCELLED'][$k['status']] ?? null;
$provider = $s['business_legal_name'] !== '' ? $s['business_legal_name'] : $s['business_name'];
$clientParty = trim((string)$k['company_name']) !== '' ? $k['company_name'] : $k['contact_name'];
$eSigned = $k['client_signed_at'] !== null;
$tz = date('T');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Agreement <?= e(Contract::displayNumber($k)) ?></title>
<style>
  @page { margin: 48px 54px 64px; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 9.6px; color: #1f2937; line-height: 1.5; }
  .top td { vertical-align: middle; }
  .logo { max-height: 44px; max-width: 200px; }
  .brand { font-size: 17px; font-weight: bold; color: #0c4a6e; }
  .agreement h1 { font-size: 15px; text-align: center; color: #0c4a6e; margin: 18px 0 2px; letter-spacing: 1px; }
  .agreement .agreement-meta { text-align: center; color: #6b7280; font-size: 8.6px; margin: 0 0 14px; }
  .agreement h2 { font-size: 10.5px; color: #0c4a6e; margin: 13px 0 4px; page-break-after: avoid; }
  .agreement p { margin: 0 0 6px; text-align: justify; }
  .agreement ol { margin: 2px 0 6px 0; padding-left: 16px; }
  .agreement li { margin-bottom: 4px; }
  .agreement-table { width: 100%; border-collapse: collapse; margin: 2px 0 8px; }
  .agreement-table td { padding: 3px 5px; border-bottom: 1px solid #e5e7eb; }
  .agreement-table td.num { text-align: right; white-space: nowrap; }
  .sign { width: 100%; margin-top: 26px; page-break-inside: avoid; }
  .sign td { width: 50%; vertical-align: bottom; padding-right: 22px; }
  .sign .who { font-size: 8px; font-weight: bold; letter-spacing: 1px; color: #0284c7; text-transform: uppercase; margin-bottom: 4px; }
  .sign .box { height: 62px; border-bottom: 1px solid #111827; }
  .sign .box img { max-height: 58px; max-width: 230px; }
  .sign .line { font-size: 8.6px; color: #374151; margin-top: 3px; }
  .cert { page-break-before: always; }
  .cert h2 { font-size: 13px; color: #0c4a6e; margin: 0 0 6px; }
  .cert table { width: 100%; border-collapse: collapse; font-size: 8.2px; }
  .cert th { background: #0c4a6e; color: #fff; text-align: left; padding: 5px; }
  .cert td { padding: 5px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  .kv td { border: 0; padding: 2px 4px; }
  .kv td.k { color: #6b7280; width: 140px; }
  .mono { font-family: "DejaVu Sans Mono", monospace; font-size: 7.6px; word-wrap: break-word; }
  .watermark { position: fixed; top: 320px; left: 40px; width: 640px; text-align: center; font-size: 96px; font-weight: bold; color: #9ca3af; opacity: .13; transform: rotate(-30deg); }
  .footer { position: fixed; bottom: -44px; left: 0; right: 0; text-align: center; font-size: 7.6px; color: #9ca3af; }
</style>
</head>
<body>
<?php if ($watermark): ?><div class="watermark"><?= $watermark ?></div><?php endif; ?>
<div class="footer">
  <?= e($provider) ?> · Agreement <?= e($k['number'] ?: 'DRAFT') ?>
  <?php if ($k['body_hash']): ?> · Document SHA-256 <?= e(substr($k['body_hash'], 0, 24)) ?>…<?php endif; ?>
</div>

<table class="top" style="width:100%">
  <tr>
    <td><?php if ($logo): ?><img src="<?= $logo ?>" class="logo" alt=""><?php else: ?><span class="brand"><?= e($s['business_name']) ?></span><?php endif; ?></td>
    <td style="text-align:right; color:#6b7280; font-size:8.6px">
      <?= e($s['business_phone']) ?><?= $s['business_phone'] && $s['business_email'] ? ' · ' : '' ?><?= e($s['business_email']) ?>
    </td>
  </tr>
</table>

<?= $body ?>

<table class="sign">
  <tr>
    <td>
      <div class="who">Client — <?= e($clientParty) ?></div>
      <div class="box"><?php if ($k['client_signature']): ?><img src="<?= e($k['client_signature']) ?>" alt=""><?php endif; ?></div>
      <div class="line">Name: <?= e($k['client_signed_name'] ?: $k['client_signer_name']) ?><?php $t = $k['client_signed_title'] ?: $k['client_signer_title']; echo $t ? ', ' . e($t) : ''; ?></div>
      <div class="line">Date: <?= $k['client_signed_at'] ? e(date('F j, Y', strtotime($k['client_signed_at']))) . ($eSigned ? ' (signed electronically)' : '') : '______________________' ?></div>
    </td>
    <td>
      <div class="who">Provider — <?= e($provider) ?></div>
      <div class="box"><?php if ($k['company_signature']): ?><img src="<?= e($k['company_signature']) ?>" alt=""><?php endif; ?></div>
      <div class="line">Name: <?= e($k['company_signer_name'] ?: ($s['business_signer_name'] ?: '______________________')) ?><?php $t = $k['company_signer_title'] ?: $s['business_signer_title']; echo $t ? ', ' . e($t) : ''; ?></div>
      <div class="line">Date: <?= $k['company_signed_at'] ? e(date('F j, Y', strtotime($k['company_signed_at']))) : '______________________' ?></div>
    </td>
  </tr>
</table>

<?php if ($eSigned): ?>
<div class="cert">
  <h2>Certificate of Electronic Signature</h2>
  <p style="margin:0 0 8px; color:#4b5563">
    This page records how and when this agreement was signed electronically under the Texas Uniform Electronic
    Transactions Act (Tex. Bus. &amp; Com. Code ch. 322) and the federal ESIGN Act. Times are shown in Central Time (<?= e($tz) ?>).
  </p>
  <table class="kv">
    <tr><td class="k">Agreement</td><td><?= e($k['title']) ?> — <?= e($k['number']) ?></td></tr>
    <tr><td class="k">Parties</td><td><?= e($provider) ?> and <?= e($clientParty) ?></td></tr>
    <tr><td class="k">Signed by (client)</td><td><?= e($k['client_signed_name']) ?><?= $k['client_signed_title'] ? ', ' . e($k['client_signed_title']) : '' ?><?= $k['client_signed_email'] ? ' — ' . e($k['client_signed_email']) : '' ?></td></tr>
    <tr><td class="k">Consent to e-records</td><td><?= e(date('M j, Y g:i:s A', strtotime($k['client_consent_at']))) ?></td></tr>
    <tr><td class="k">Client signed at</td><td><?= e(date('M j, Y g:i:s A', strtotime($k['client_signed_at']))) ?> from IP <?= e($k['client_ip']) ?></td></tr>
    <tr><td class="k">Client device</td><td class="mono"><?= e($k['client_user_agent']) ?></td></tr>
    <?php if ($k['company_signed_at']): ?>
    <tr><td class="k">Countersigned</td><td><?= e($k['company_signer_name']) ?> — <?= e(date('M j, Y g:i:s A', strtotime($k['company_signed_at']))) ?></td></tr>
    <?php endif; ?>
    <tr><td class="k">Document fingerprint</td><td class="mono">SHA-256 <?= e($k['body_hash']) ?></td></tr>
  </table>
  <p style="margin:8px 0; color:#4b5563">
    The fingerprint is computed from the agreement text locked when it was finalized; the client signed that exact text.
    Any change to the text would produce a different fingerprint.
  </p>
  <table>
    <thead><tr><th style="width:110px">Date / time</th><th style="width:160px">Event</th><th>Details</th><th style="width:80px">IP address</th></tr></thead>
    <tbody>
    <?php foreach ($events as $ev): if ($ev['event'] === 'pdf_downloaded') continue; ?>
      <tr>
        <td><?= e(date('M j, Y g:i:s A', strtotime($ev['created_at']))) ?></td>
        <td><?= e(Contract::EVENTS[$ev['event']] ?? $ev['event']) ?></td>
        <td><?= e($ev['user_name'] ? 'By ' . $ev['user_name'] . ($ev['details'] !== '' ? ' — ' : '') : '') ?><?= e($ev['details']) ?></td>
        <td><?= e($ev['ip'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
</body>
</html>
