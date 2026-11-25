<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/brief-schema.php';
require_once __DIR__ . '/../includes/brief-helpers.php';

ninubo_admin_require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = ninubo_db()->prepare('SELECT * FROM client_briefs WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) {
    http_response_code(404);
    $pageTitle = 'Cliente no encontrado | NINUBO';
    $pageNoindex = true;
    require __DIR__ . '/../includes/head.php';
    echo '<main class="admin-main"><div class="container"><div class="admin-card"><h1>Cliente no encontrado</h1><p><a class="btn btn-primary" href="' . ninubo_url('admin/index.php') . '">Volver al panel</a></p></div></div></main></body></html>';
    exit;
}

$schema = ninubo_brief_schema();
$data = [];
if ($client['data']) {
    $decoded = json_decode($client['data'], true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}

$statusLabels = ['pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'completado' => 'Completado'];
$link = ninubo_absolute_url('brief.php', 'es') . '?token=' . $client['token'];

$pageTitle = htmlspecialchars($client['client_label']) . ' | Panel admin | NINUBO';
$pageDesc = 'Panel administrativo de NINUBO.';
$pageNoindex = true;
require __DIR__ . '/../includes/head.php';
?>
<header class="admin-topbar">
  <div class="container admin-topbar-inner">
    <a href="<?= ninubo_url('admin/index.php') ?>" class="logo">
      <img src="<?= ninubo_url('assets/img/ninubo.png') ?>" alt="NINUBO" width="130" height="34">
    </a>
    <?= ninubo_admin_logout_button() ?>
  </div>
</header>

<main class="admin-main">
  <div class="container brief-container">
    <p><a href="<?= ninubo_url('admin/index.php') ?>" style="color:var(--color-text-muted);">&larr; Volver al panel</a></p>

    <div class="admin-card">
      <span class="admin-badge admin-badge--<?= htmlspecialchars($client['status']) ?>"><?= htmlspecialchars($statusLabels[$client['status']] ?? $client['status']) ?></span>
      <h1 style="margin:0.75rem 0 0.5rem;"><?= htmlspecialchars($client['client_label']) ?></h1>
      <p style="color:var(--color-text-muted);font-size:0.9rem;">
        Creado: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($client['created_at']))) ?> ·
        Actualizado: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($client['updated_at']))) ?>
        <?php if ($client['completed_at']): ?> · Completado: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($client['completed_at']))) ?><?php endif; ?>
        · Idioma del formulario: <?= $client['lang'] === 'en' ? '🇺🇸 Inglés' : '🇲🇽 Español' ?>
      </p>
      <p style="margin-top:0.75rem;word-break:break-all;">
        <button type="button" class="admin-link-copy" data-copy="<?= htmlspecialchars($link) ?>">Copiar enlace del cliente</button>
        <span style="color:var(--color-text-muted);font-size:0.85rem;"><?= htmlspecialchars($link) ?></span>
      </p>
    </div>

    <?php if (!$data): ?>
      <div class="admin-card"><p style="color:var(--color-text-muted);">Este cliente todavía no ha llenado ninguna información.</p></div>
    <?php else: ?>
      <?php foreach ($schema as $step): ?>
        <div class="admin-card">
          <h2 style="margin-bottom:1.25rem;"><?= htmlspecialchars($step['title']) ?></h2>
          <?php foreach ($step['fields'] as $fieldName => $field): ?>
            <?= ninubo_brief_render_view($fieldName, $field, $data[$fieldName] ?? null) ?>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>
<script>
document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-copy]');
  if (!btn) return;
  navigator.clipboard.writeText(btn.getAttribute('data-copy')).then(function () {
    var original = btn.textContent;
    btn.textContent = '¡Copiado!';
    setTimeout(function () { btn.textContent = original; }, 1500);
  });
});
</script>
</body>
</html>
