<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/brief-schema.php';
require_once __DIR__ . '/includes/brief-helpers.php';

// Idioma del formulario del cliente — usa el mismo mecanismo ?lang= del resto
// del sitio (ver includes/config.php), pero solo afecta esta página: el
// esquema de preguntas se resuelve al idioma elegido (ver brief-schema.php).
$lang = NINUBO_LANG;
$t = fn(string $es, string $en): string => $lang === 'en' ? $en : $es;

$token = trim($_GET['token'] ?? '');
$client = null;
$dbError = false;

if ($token !== '') {
    try {
        $stmt = ninubo_db()->prepare('SELECT * FROM client_briefs WHERE token = ? LIMIT 1');
        $stmt->execute([$token]);
        $client = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        $dbError = true;
    }
}

// Fuerza idioma 'es' al construir la ruta base: ninubo_url() agrega su propio
// "?lang=en" cuando NINUBO_LANG ya es 'en', lo que generaría una URL con dos
// "?" (?lang=en?token=...) y perdería el token. Aquí armamos el query string
// nosotros mismos con un solo "?".
$briefPath = ninubo_url('brief.php', 'es');
$langUrlEs = $briefPath . '?token=' . urlencode($token) . '&lang=es';
$langUrlEn = $briefPath . '?token=' . urlencode($token) . '&lang=en';

// Si el cliente ya había empezado a llenar en inglés, seguimos en ese idioma
// aunque el enlace se haya abierto sin ?lang= (para que "continuar más tarde"
// no lo regrese a español a medio llenado). El switcher de idioma siempre manda
// ?lang= explícito (arriba), así que esto solo aplica al abrir el link "pelón".
if ($client && !isset($_GET['lang']) && ($client['lang'] ?? 'es') === 'en') {
    header('Location: ' . $langUrlEn);
    exit;
}

// Las preguntas dependen del paquete contratado (perfil guardado al crear el link en el CRM).
$schema = ninubo_brief_schema($lang, $client['brief_profile'] ?? null);
$stepKeys = array_keys($schema);
$data = [];
if ($client && $client['data']) {
    $decoded = json_decode($client['data'], true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

$pageTitle = $t('Información para su página web | NINUBO', 'Information for your website | NINUBO');
$pageDesc = $t('Formulario para recopilar la información inicial de su página web con NINUBO.', 'Form to collect the initial information for your website with NINUBO.');
$pageNoindex = true;
require __DIR__ . '/includes/head.php';
?>
<header class="brief-topbar">
  <div class="container brief-topbar-inner">
    <a href="https://ninubo.com" target="_blank" rel="noopener" class="logo">
      <img src="<?= ninubo_url('assets/img/ninubo.png') ?>" alt="NINUBO" width="130" height="34">
    </a>
    <?php if ($client && !$dbError): ?>
    <div class="language-switcher" aria-label="Idioma / Language">
      <a href="<?= ninubo_brief_esc($langUrlEs) ?>" class="language-option <?= $lang === 'es' ? 'is-active' : '' ?>" lang="es" aria-label="Español">
        <span aria-hidden="true">🇲🇽</span><span>ES</span>
      </a>
      <a href="<?= ninubo_brief_esc($langUrlEn) ?>" class="language-option <?= $lang === 'en' ? 'is-active' : '' ?>" lang="en" aria-label="English">
        <span aria-hidden="true">🇺🇸</span><span>EN</span>
      </a>
    </div>
    <?php endif; ?>
  </div>
</header>

<main class="brief-main">
  <div class="container brief-container">

<?php if ($dbError): ?>
    <div class="brief-card brief-status-card">
      <h1><?= $t('No pudimos cargar el formulario', 'We could not load the form') ?></h1>
      <p><?= $t('Ocurrió un problema técnico. Por favor intente de nuevo en unos minutos o contáctenos directamente.', 'A technical issue occurred. Please try again in a few minutes or contact us directly.') ?></p>
      <p><a class="btn btn-primary" href="mailto:contacto@ninubo.com">contacto@ninubo.com</a></p>
    </div>

<?php elseif (!$client): ?>
    <div class="brief-card brief-status-card">
      <h1><?= $t('Enlace no válido', 'Invalid link') ?></h1>
      <p><?= $t('Este enlace no existe o ha expirado. Por favor contacte a NINUBO para obtener su enlace correcto.', 'This link does not exist or has expired. Please contact NINUBO to get your correct link.') ?></p>
      <p><a class="btn btn-primary" href="https://wa.me/528995083462" target="_blank" rel="noopener"><?= $t('Escribir por WhatsApp', 'Message us on WhatsApp') ?></a></p>
    </div>

<?php elseif ($client['status'] === 'completado'): ?>
    <div class="brief-card brief-status-card">
      <h1><?= $t('¡Gracias, ya recibimos su información!', 'Thank you, we already received your information!') ?></h1>
      <p><?= $t('Recibimos su formulario correctamente. Nuestro equipo lo revisará y se pondrá en contacto con usted para confirmar los siguientes pasos.', 'We received your form successfully. Our team will review it and get in touch with you to confirm next steps.') ?></p>
      <p><?= $t('Si necesita corregir o agregar algo, escríbanos directamente.', 'If you need to correct or add anything, please write to us directly.') ?></p>
      <p><a class="btn btn-primary" href="https://wa.me/528995083462" target="_blank" rel="noopener"><?= $t('Escribir por WhatsApp', 'Message us on WhatsApp') ?></a></p>
    </div>

<?php else: ?>
    <div class="brief-intro">
      <p class="hero-badge"><span class="badge-dot"></span> <?= ninubo_brief_esc($client['client_label']) ?></p>
      <h1><?= $t('Información para iniciar su página web', 'Information to start your website') ?></h1>
      <p class="brief-lead"><?= $t('Gracias por confiar en nosotros para desarrollar su página web. Para comenzar con el proyecto necesitamos recopilar la siguiente información.', 'Thank you for trusting us to build your website. To start the project, we need to gather the following information.') ?></p>
      <p class="brief-lead brief-lead-muted"><?= $t('No es necesario que tenga todo listo; si algún elemento no está disponible, podemos apoyarle durante el proceso. Su avance se guarda automáticamente, así que puede continuar más tarde desde este mismo enlace.', 'You don\'t need to have everything ready — if something isn\'t available, we can help you along the way. Your progress saves automatically, so you can continue later from this same link.') ?></p>
    </div>

    <noscript>
      <div class="form-alert form-alert--error is-visible"><?= $t('Este formulario necesita JavaScript habilitado para funcionar. Por favor actívelo en su navegador.', 'This form needs JavaScript enabled to work. Please enable it in your browser.') ?></div>
    </noscript>

    <div class="brief-progress-wrap">
      <div class="brief-progress-bar"><div class="brief-progress-fill" id="briefProgressFill"></div></div>
      <div class="brief-progress-label" id="briefProgressLabel"><?= $t('Paso', 'Step') ?> 1 <?= $t('de', 'of') ?> <?= count($stepKeys) ?></div>
    </div>

    <div class="brief-jump-wrap">
      <label for="briefJump"><?= $t('Ir directamente a la sección:', 'Jump directly to a section:') ?></label>
      <select id="briefJump">
        <?php foreach ($stepKeys as $i => $key): ?>
          <option value="<?= $i + 1 ?>"><?= $i + 1 ?>. <?= ninubo_brief_esc($schema[$key]['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-alert" id="briefAlert" role="alert"></div>

    <form id="briefForm" class="brief-form" novalidate>
      <input type="hidden" name="token" value="<?= ninubo_brief_esc($token) ?>">
      <input type="hidden" name="lang" value="<?= ninubo_brief_esc($lang) ?>">
      <input type="hidden" name="current_step" id="briefCurrentStep" value="<?= (int)($client['current_step'] ?: 1) ?>">
      <div class="hp-field" aria-hidden="true">
        <label for="hp_url">URL</label>
        <input type="text" id="hp_url" name="hp_url" tabindex="-1" autocomplete="off">
      </div>

      <?php foreach ($stepKeys as $i => $key):
        $step = $schema[$key];
        $stepNum = $i + 1;
      ?>
      <section class="wizard-step" data-step="<?= $stepNum ?>">
        <div class="brief-card">
          <p class="section-tag"><?= $t('Paso', 'Step') ?> <?= $stepNum ?> <?= $t('de', 'of') ?> <?= count($stepKeys) ?></p>
          <h2><?= ninubo_brief_esc($step['title']) ?></h2>
          <?php if (!empty($step['desc'])): ?><p class="brief-step-desc"><?= ninubo_brief_esc($step['desc']) ?></p><?php endif; ?>

          <?php foreach ($step['fields'] as $fieldName => $field): ?>
            <?= ninubo_brief_render_field($fieldName, $field, $data[$fieldName] ?? null) ?>
          <?php endforeach; ?>

          <div class="brief-nav">
            <?php if ($stepNum > 1): ?>
              <button type="button" class="btn btn-ghost brief-prev"><?= $t('Anterior', 'Previous') ?></button>
            <?php else: ?>
              <span></span>
            <?php endif; ?>
            <?php if ($stepNum < count($stepKeys)): ?>
              <button type="button" class="btn btn-primary brief-next"><?= $t('Siguiente', 'Next') ?></button>
            <?php else: ?>
              <button type="submit" class="btn btn-primary" id="briefSubmitBtn"><?= $t('Enviar información', 'Submit information') ?></button>
            <?php endif; ?>
          </div>
          <p class="brief-save-status" id="briefSaveStatus" aria-live="polite"></p>
        </div>
      </section>
      <?php endforeach; ?>
    </form>
<?php endif; ?>

  </div>
</main>

<footer class="brief-footer">
  <div class="container">
    <p>&copy; <span id="year"></span> NINUBO. <?= $t('¿Dudas? Escríbanos a', 'Questions? Write to us at') ?> <a href="mailto:contacto@ninubo.com">contacto@ninubo.com</a> <?= $t('o al', 'or call') ?> <a href="tel:+528995083462">8995-08-3462</a>.</p>
  </div>
</footer>
<a href="https://wa.me/528995083462"
   target="_blank"
   rel="noopener"
   aria-label="<?= $t('Contáctanos por WhatsApp', 'Contact us on WhatsApp') ?>"
   style="position:fixed;bottom:28px;left:28px;z-index:9999;display:flex;align-items:center;justify-content:center;width:60px;height:60px;background-color:#25d366;border-radius:50%;box-shadow:0 4px 20px rgba(37,211,102,0.5),0 2px 8px rgba(0,0,0,0.3);text-decoration:none;transition:transform 0.2s ease;">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32" style="fill:#fff;flex-shrink:0;display:block;" aria-hidden="true">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
  </svg>
</a>
<script>
  window.NINUBO_BASE = <?= json_encode(NINUBO_BASE) ?>;
  window.NINUBO_BRIEF_LANG = <?= json_encode($lang) ?>;
</script>
<script src="<?= ninubo_url('assets/js/brief-wizard.js') ?>"></script>
</body>
</html>
