<?php
/**
 * Public signing page (rendered by /sign.php). English, with a short Spanish help line.
 * @var ?array $k @var array $s @var array $errors @var array $post @var string $self @var ?string $logo @var bool $justSigned
 */
$provider = $s['business_legal_name'] !== '' ? $s['business_legal_name'] : $s['business_name'];
$contactBits = array_filter([$s['business_phone'], $s['business_email']]);
$clientParty = $k ? (trim((string)$k['company_name']) !== '' ? $k['company_name'] : $k['contact_name']) : '';
$p = fn(string $key, string $default = '') => e($post[$key] ?? $default);
$asset = fn(string $path) => asset($path);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($k ? $k['title'] . ' ' . $k['number'] : 'Agreement') ?> · <?= e($s['business_name']) ?></title>
  <link rel="icon" href="<?= e(NINUBO_BASE . '/favicon.ico') ?>">
  <link rel="stylesheet" href="<?= $asset('tabler/css/tabler.min.css') ?>">
  <link rel="stylesheet" href="<?= $asset('tabler/icons/tabler-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= $asset('app/app.css') ?>">
  <style>body { background: #f1f5f9; } .sign-brand { font-weight: 700; font-size: 1.35rem; color: #0c4a6e; } .sign-logo { max-height: 44px; }</style>
</head>
<body>
<div class="page">
  <header class="navbar navbar-expand-md d-print-none bg-white border-bottom">
    <div class="container-xl">
      <?php if ($logo): ?><img src="<?= $logo ?>" alt="<?= e($s['business_name']) ?>" class="sign-logo"><?php else: ?><span class="sign-brand"><?= e($s['business_name']) ?></span><?php endif; ?>
      <?php if ($contactBits): ?><div class="ms-auto small text-secondary d-none d-md-block"><?= e(implode(' · ', $contactBits)) ?></div><?php endif; ?>
    </div>
  </header>

  <div class="page-wrapper">
    <div class="page-body">
      <div class="container-xl" style="max-width: 920px;">

<?php if (!$k): ?>
        <div class="card"><div class="card-body text-center py-5">
          <i class="ti ti-link-off text-secondary" style="font-size:3rem"></i>
          <h2 class="mt-2">This link is not valid</h2>
          <p class="text-secondary">Please check the link or contact <?= e($s['business_name']) ?><?= $contactBits ? ': ' . e(implode(' · ', $contactBits)) : '' ?>.</p>
          <p class="text-secondary small">Este enlace no es válido. Por favor contáctenos.</p>
        </div></div>

<?php elseif ($k['status'] === 'cancelled'): ?>
        <div class="card"><div class="card-body text-center py-5">
          <i class="ti ti-ban text-secondary" style="font-size:3rem"></i>
          <h2 class="mt-2">This agreement was cancelled</h2>
          <p class="text-secondary">Agreement <?= e($k['number']) ?> is no longer available for signature. Please contact <?= e($s['business_name']) ?> if you have questions.</p>
        </div></div>

<?php else: ?>
        <div class="mb-3">
          <div class="text-secondary small text-uppercase fw-bold">Agreement <?= e($k['number']) ?></div>
          <h1 class="h2 mb-1"><?= e($k['title']) ?></h1>
          <div class="text-secondary">Between <?= e($provider) ?> and <?= e($clientParty) ?></div>
        </div>

        <?php if ($justSigned || in_array($k['status'], ['client_signed', 'signed'], true)): ?>
        <div class="alert alert-success">
          <div class="d-flex gap-2">
            <i class="ti ti-circle-check" style="font-size:1.5rem"></i>
            <div>
              <div class="fw-bold">Signed<?= $k['client_signed_name'] ? ' by ' . e($k['client_signed_name']) : '' ?><?= $k['client_signed_at'] ? ' on ' . e(date('F j, Y \a\t g:i A', strtotime($k['client_signed_at']))) : '' ?>.</div>
              <div>Thank you! <?= $k['status'] === 'signed' ? 'The agreement is fully signed by both parties.' : e($s['business_name']) . ' will countersign it and confirm the next steps.' ?>
                Keep a copy for your records.</div>
              <div class="small mt-1">¡Gracias! Su firma quedó registrada. Descargue una copia para sus archivos.</div>
              <a href="<?= e($self) ?>&amp;pdf=1" class="btn btn-success btn-sm mt-2"><i class="ti ti-download me-1"></i>Download a copy (PDF)</a>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info small mb-3">
          Please read the agreement below and sign at the bottom of the page.
          <span class="d-block mt-1">El contrato está en inglés. Si tiene dudas o prefiere que se lo expliquemos en español, contáctenos<?= $contactBits ? ': ' . e(implode(' · ', $contactBits)) : '' ?>.</span>
        </div>
        <?php endif; ?>

        <div class="card mb-3">
          <div class="card-header">
            <h3 class="card-title">Agreement text</h3>
            <div class="card-actions"><a href="<?= e($self) ?>&amp;pdf=1" class="btn btn-sm"><i class="ti ti-download me-1"></i>PDF</a></div>
          </div>
          <div class="crm-agreement-scroll"><?= $k['body_html'] ?></div>
        </div>

        <?php if ($k['status'] === 'sent' && !$justSigned): ?>
        <form method="post" action="<?= e($self) ?>" class="card mb-4" id="sign-form" novalidate>
          <input type="hidden" name="doc_hash" value="<?= e($k['body_hash']) ?>">
          <div class="card-header"><h3 class="card-title"><i class="ti ti-signature me-2"></i>Sign this agreement</h3></div>
          <div class="card-body">
            <?php if ($errors): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
            <?php endif; ?>

            <div class="mb-4 p-3 rounded border bg-light-subtle small">
              <div class="fw-bold mb-1">Electronic records and signature disclosure</div>
              <ul class="mb-2 ps-3">
                <li>You agree to receive this agreement and sign it electronically. Your electronic signature has the same legal effect as a handwritten one.</li>
                <li>You may instead ask for a paper copy to sign by hand, at no cost, by contacting <?= e($s['business_name']) ?><?= $contactBits ? ' (' . e(implode(' · ', $contactBits)) . ')' : '' ?>. You may withdraw this consent at any time before signing.</li>
                <li>To view and keep this agreement you need a web browser and a PDF reader. You can download a copy at any time from this page.</li>
              </ul>
              <label class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="consent" value="1"<?= !empty($post['consent']) ? ' checked' : '' ?>>
                <span class="form-check-label fw-medium">I agree to use electronic records and signatures for this agreement.</span>
              </label>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label required" for="signer_name">Full legal name</label>
                <input type="text" class="form-control" id="signer_name" name="signer_name" value="<?= $p('signer_name', $k['client_signer_name']) ?>" autocomplete="name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="signer_title">Title</label>
                <input type="text" class="form-control" id="signer_title" name="signer_title" value="<?= $p('signer_title', $k['client_signer_title']) ?>" placeholder="e.g. Owner">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="signer_email">Email</label>
                <input type="email" class="form-control" id="signer_email" name="signer_email" value="<?= $p('signer_email', $k['client_signer_email']) ?>" autocomplete="email">
              </div>
              <div class="col-12">
                <label class="form-label required">Signature</label>
                <div class="crm-signature" data-signature-pad>
                  <canvas></canvas>
                  <button type="button" class="btn btn-sm btn-ghost-secondary" data-signature-clear>Clear</button>
                  <input type="hidden" name="signature">
                </div>
                <div class="form-hint">Draw your signature with your finger, a pen or the mouse.</div>
              </div>
              <div class="col-12">
                <label class="form-check">
                  <input class="form-check-input" type="checkbox" name="agree" value="1"<?= !empty($post['agree']) ? ' checked' : '' ?>>
                  <span class="form-check-label">I have read and agree to the terms of this agreement, and I am authorized to sign it on behalf of <strong><?= e($clientParty) ?></strong>.</span>
                </label>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex flex-wrap align-items-center gap-2">
            <span class="small text-secondary">Your name, signature, the date and time, your IP address and device are recorded as proof of signature.</span>
            <button type="submit" class="btn btn-primary ms-auto" id="sign-submit" disabled><i class="ti ti-signature me-1"></i>Sign agreement</button>
          </div>
        </form>
        <?php endif; ?>
<?php endif; ?>

        <p class="text-center text-secondary small mb-4">&copy; <?= date('Y') ?> <?= e($s['business_name']) ?></p>
      </div>
    </div>
  </div>
</div>
<?php if ($k && $k['status'] === 'sent' && !$justSigned): ?>
<script src="<?= $asset('app/signature-pad.js') ?>"></script>
<script>
  (function () {
    var form = document.getElementById('sign-form');
    var btn = document.getElementById('sign-submit');
    function check() {
      btn.disabled = !(form.consent.checked && form.agree.checked && form.signer_name.value.trim().length >= 3 && form.signature.value);
    }
    form.addEventListener('change', check);
    form.addEventListener('input', check);
    form.addEventListener('submit', function () { btn.disabled = true; btn.textContent = 'Signing…'; });
    check();
  })();
</script>
<?php endif; ?>
</body>
</html>
