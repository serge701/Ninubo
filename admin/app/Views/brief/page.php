<?php
/**
 * Public website brief (rendered by /brief.php). Spanish or English; Tabler, light by default with a dark toggle.
 * @var ?array $brief @var bool $dbError @var string $lang @var array $schema @var array $data @var array $s
 * @var ?string $logo @var string $self
 */
use Crm\Core\BriefForm;

$t = fn(string $es, string $en): string => $lang === 'en' ? $en : $es;
$steps = array_values($schema);
$keys = array_keys($schema);
$total = count($steps);
$open = $brief && $brief['status'] !== 'completado';
$current = $brief ? max(1, min($total, (int)($brief['current_step'] ?: 1))) : 1;

// Contact options for the client (from Settings — never hard-coded).
$waDigits = preg_replace('/\D+/', '', (string)$s['business_whatsapp']);
$waUrl = $waDigits !== '' ? 'https://wa.me/' . (strlen($waDigits) === 10 ? '1' . $waDigits : $waDigits) : '';
$phone = (string)$s['business_phone'];
$email = (string)$s['business_email'];
$contactLine = function () use ($t, $phone, $email): string {
    $bits = [];
    if ($email !== '') {
        $bits[] = '<a href="mailto:' . e($email) . '">' . e($email) . '</a>';
    }
    if ($phone !== '') {
        $bits[] = '<a href="tel:' . e(preg_replace('/[^\d+]/', '', $phone)) . '">' . e($phone) . '</a>';
    }
    return implode(' ' . $t('o', 'or') . ' ', $bits);
};
$langUrl = fn(string $l): string => $self . '&lang=' . $l;
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($t('Información para su página web', 'Information for your website')) ?> · <?= e($s['business_name']) ?></title>
  <link rel="icon" href="<?= e(NINUBO_BASE . '/favicon.ico') ?>">
  <script>
    (function () {
      var t = 'light';
      try { t = localStorage.getItem('brief-theme') || 'light'; } catch (e) {}
      document.documentElement.setAttribute('data-bs-theme', t);
    })();
  </script>
  <link rel="stylesheet" href="<?= asset('tabler/css/tabler.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('tabler/icons/tabler-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('app/app.css') ?>">
  <style>
    .brief-brand { font-weight: 700; font-size: 1.35rem; color: var(--tblr-primary); }
    .brief-logo { max-height: 40px; }
    @media (min-width: 992px) { .brief-steps { top: 4.5rem; } }
    .brief-steps .list-group-item { padding: .5rem .75rem; }
    .brief-steps .list-group-item.active { z-index: 0; }
    .brief-step-num { width: 1.5rem; height: 1.5rem; font-size: .75rem; }
    .brief-step-done .brief-step-num { background: var(--tblr-green); color: #fff; }
    .form-selectgroup-label { white-space: normal; text-align: start; }
    .wizard-step { scroll-margin-top: 5rem; }
  </style>
</head>
<body>
<div class="page">
  <header class="navbar navbar-expand bg-body border-bottom sticky-top d-print-none">
    <div class="container-xl">
      <?php if ($logo): ?><img src="<?= $logo ?>" alt="<?= e($s['business_name']) ?>" class="brief-logo"><?php else: ?><span class="brief-brand"><?= e($s['business_name']) ?></span><?php endif; ?>
      <div class="ms-auto d-flex align-items-center gap-2">
        <?php if ($brief): ?>
        <div class="btn-group btn-group-sm" role="group" aria-label="Idioma / Language">
          <a href="<?= e($langUrl('es')) ?>" class="btn<?= $lang === 'es' ? ' btn-primary' : '' ?>" lang="es" data-lang-link<?= $lang === 'es' ? ' aria-current="true"' : '' ?>>ES</a>
          <a href="<?= e($langUrl('en')) ?>" class="btn<?= $lang === 'en' ? ' btn-primary' : '' ?>" lang="en" data-lang-link<?= $lang === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
        </div>
        <?php endif; ?>
        <?php if ($waUrl): ?>
        <a href="<?= e($waUrl) ?>" class="btn btn-sm btn-success" target="_blank" rel="noopener" title="WhatsApp"><i class="ti ti-brand-whatsapp"></i><span class="d-none d-sm-inline ms-1"><?= $t('¿Dudas?', 'Questions?') ?></span></a>
        <?php endif; ?>
        <a href="#" class="btn btn-sm btn-icon" data-theme-toggle title="<?= e($t('Modo claro / oscuro', 'Light / dark mode')) ?>" aria-label="<?= e($t('Modo claro / oscuro', 'Light / dark mode')) ?>">
          <i class="ti ti-moon crm-theme-icon-dark"></i><i class="ti ti-sun crm-theme-icon-light"></i>
        </a>
      </div>
    </div>
  </header>

  <div class="page-wrapper">
    <div class="page-body">
      <div class="container-xl">

<?php if ($dbError || !$brief): ?>
        <div class="card mx-auto" style="max-width: 640px"><div class="card-body text-center py-5">
          <i class="ti <?= $dbError ? 'ti-plug-connected-x' : 'ti-link-off' ?> text-secondary" style="font-size:3rem"></i>
          <h1 class="h2 mt-2"><?= $dbError ? $t('No pudimos cargar el formulario', 'We could not load the form') : $t('Enlace no válido', 'Invalid link') ?></h1>
          <p class="text-secondary"><?= $dbError
              ? $t('Ocurrió un problema técnico. Intente de nuevo en unos minutos.', 'A technical problem occurred. Please try again in a few minutes.')
              : $t('Este enlace no existe o ya no está activo. Pídanos su enlace correcto.', 'This link does not exist or is no longer active. Ask us for your correct link.') ?></p>
          <?php if ($contactLine()): ?><p class="mb-0"><?= $t('Escríbanos a', 'Write to us at') ?> <?= $contactLine() ?>.</p><?php endif; ?>
        </div></div>

<?php elseif (!$open): ?>
        <div class="card mx-auto" style="max-width: 640px"><div class="card-body text-center py-5">
          <i class="ti ti-circle-check text-green" style="font-size:3rem"></i>
          <h1 class="h2 mt-2"><?= $t('¡Gracias, ya recibimos su información!', 'Thank you, we received your information!') ?></h1>
          <p class="text-secondary"><?= $t('Nuestro equipo la revisará y se pondrá en contacto con usted para confirmar los siguientes pasos.', 'Our team will review it and get in touch with you to confirm the next steps.') ?></p>
          <?php if ($contactLine()): ?><p class="mb-0"><?= $t('¿Necesita corregir o agregar algo? Escríbanos a', 'Need to correct or add something? Write to us at') ?> <?= $contactLine() ?>.</p><?php endif; ?>
        </div></div>

<?php else: ?>
        <div class="mb-4" style="max-width: 760px">
          <div class="text-secondary small text-uppercase fw-bold"><?= e($brief['client_label']) ?><?= $brief['package_name'] ? ' · ' . e($brief['package_name']) : '' ?></div>
          <h1 class="h2 mt-1 mb-2"><?= $t('Información para iniciar su página web', 'Information to start your website') ?></h1>
          <p class="text-secondary mb-0">
            <?= $t('Gracias por confiar en nosotros. Para comenzar necesitamos la siguiente información. No es necesario tenerlo todo: lo que falte lo vemos juntos. Su avance se guarda automáticamente y puede continuar después desde este mismo enlace.',
                   'Thank you for trusting us. To get started we need the following information. You don\'t need to have everything: we\'ll go over anything missing together. Your progress saves automatically and you can continue later from this same link.') ?>
          </p>
        </div>

        <noscript><div class="alert alert-danger"><?= $t('Este formulario necesita JavaScript. Actívelo en su navegador.', 'This form needs JavaScript. Please enable it in your browser.') ?></div></noscript>

        <div class="row g-4">
          <div class="col-lg-4 col-xl-3">
            <div class="card brief-steps sticky-lg-top">
              <div class="card-body pb-2">
                <div class="d-flex justify-content-between small mb-1">
                  <span id="briefProgressLabel"><?= $t('Paso', 'Step') ?> <?= $current ?> <?= $t('de', 'of') ?> <?= $total ?></span>
                  <span class="text-secondary" id="briefSaveStatus" aria-live="polite"></span>
                </div>
                <div class="progress progress-sm"><div class="progress-bar" id="briefProgressFill" style="width: <?= round($current / $total * 100) ?>%"></div></div>
                <label class="form-label small text-secondary mt-3 mb-1 d-lg-none" for="briefJump"><?= $t('Ir a la sección', 'Go to section') ?></label>
                <select class="form-select d-lg-none mb-2" id="briefJump">
                  <?php foreach ($steps as $i => $step): ?><option value="<?= $i + 1 ?>"<?= $i + 1 === $current ? ' selected' : '' ?>><?= $i + 1 ?>. <?= e($step['title']) ?></option><?php endforeach; ?>
                </select>
              </div>
              <div class="list-group list-group-flush d-none d-lg-block" id="briefStepList">
                <?php foreach ($steps as $i => $step): ?>
                <a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-2<?= $i + 1 === $current ? ' active' : '' ?>" data-go="<?= $i + 1 ?>">
                  <span class="avatar rounded-circle brief-step-num"><?= $i + 1 ?></span>
                  <span class="flex-fill small"><?= e($step['title']) ?></span>
                  <?php if (!$step['summary']): ?><span class="badge bg-transparent text-secondary small brief-step-count"></span><?php endif; ?>
                </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="col-lg-8 col-xl-9">
            <div class="alert d-none" id="briefAlert" role="alert"></div>

            <form id="briefForm" novalidate>
              <input type="hidden" name="token" value="<?= e($brief['token']) ?>">
              <input type="hidden" name="lang" value="<?= e($lang) ?>">
              <input type="hidden" name="current_step" id="briefCurrentStep" value="<?= $current ?>">
              <div class="d-none" aria-hidden="true"><label for="hp_url">URL</label><input type="text" id="hp_url" name="hp_url" tabindex="-1" autocomplete="off"></div>

              <?php foreach ($steps as $i => $step): $n = $i + 1; ?>
              <section class="card wizard-step" data-step="<?= $n ?>" data-key="<?= e($keys[$i]) ?>"<?= $step['summary'] ? ' data-summary' : '' ?><?= $n === $current ? '' : ' hidden' ?>>
                <div class="card-header d-block">
                  <div class="text-secondary small"><?= $t('Paso', 'Step') ?> <?= $n ?> <?= $t('de', 'of') ?> <?= $total ?></div>
                  <h2 class="card-title h3 mb-0"><?= e($step['title']) ?></h2>
                  <?php if ($step['desc'] !== ''): ?><div class="text-secondary mt-1"><?= e($step['desc']) ?></div><?php endif; ?>
                </div>
                <div class="card-body">
                  <?php if ($step['summary']): ?><div id="briefSummary" class="mb-4"></div><?php endif; ?>
                  <?php foreach ($step['fields'] as $name => $field): ?>
                    <?= BriefForm::field($name, $field, $data[$name] ?? null, $lang) ?>
                  <?php endforeach; ?>
                </div>
                <div class="card-footer d-flex align-items-center gap-2">
                  <?php if ($n > 1): ?><button type="button" class="btn brief-prev"><i class="ti ti-arrow-left me-1"></i><?= $t('Anterior', 'Previous') ?></button><?php endif; ?>
                  <?php if ($n < $total): ?>
                  <button type="button" class="btn btn-primary ms-auto brief-next"><?= $t('Siguiente', 'Next') ?><i class="ti ti-arrow-right ms-1"></i></button>
                  <?php else: ?>
                  <button type="submit" class="btn btn-success ms-auto" id="briefSubmitBtn"><i class="ti ti-send me-1"></i><?= $t('Enviar información', 'Submit information') ?></button>
                  <?php endif; ?>
                </div>
              </section>
              <?php endforeach; ?>
            </form>
          </div>
        </div>
<?php endif; ?>

        <p class="text-center text-secondary small my-4">
          &copy; <?= date('Y') ?> <?= e($s['business_name']) ?><?php if ($contactLine()): ?> · <?= $t('¿Dudas? Escríbanos a', 'Questions? Write to us at') ?> <?= $contactLine() ?><?php endif; ?>
        </p>
      </div>
    </div>
  </div>
</div>
<script>
  window.BRIEF = <?= json_encode([
      'saveUrl' => ninubo_url('brief.php', 'es'),
      'lang' => $lang,
      'text' => [
          'saving' => $t('Guardando…', 'Saving…'),
          'saved' => $t('Guardado', 'Saved'),
          'saveError' => $t('No se pudo guardar; revise su conexión.', 'Could not save; check your connection.'),
          'sending' => $t('Enviando…', 'Sending…'),
          'connError' => $t('Error de conexión. Intente de nuevo.', 'Connection error. Please try again.'),
          'step' => $t('Paso', 'Step'),
          'of' => $t('de', 'of'),
          'answered' => $t('respondidas', 'answered'),
          'go' => $t('Ir', 'Go'),
          'complete' => $t('Completa', 'Complete'),
          'partial' => $t('Parcial', 'Partial'),
          'empty' => $t('Sin llenar', 'Not started'),
          'summaryTitle' => $t('Su avance', 'Your progress'),
          'requiredMissing' => $t('Falta completar:', 'Still required:'),
          'pickOne' => $t('Elija al menos una opción.', 'Choose at least one option.'),
      ],
  ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?= asset('app/brief.js') ?>"></script>
</body>
</html>
