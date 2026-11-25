<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/brief-helpers.php';

ninubo_admin_require_login();

$pdo = ninubo_db();
$notice = '';
$newLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ninubo_admin_require_csrf();
    $intent = $_POST['intent'] ?? '';

    if ($intent === 'create') {
        $label = trim($_POST['client_label'] ?? '');
        if ($label === '') {
            $notice = 'Escribe un nombre para identificar al cliente.';
        } else {
            $token = ninubo_brief_new_token();
            $stmt = $pdo->prepare('INSERT INTO client_briefs (token, client_label) VALUES (?, ?)');
            $stmt->execute([$token, $label]);
            $newLink = ninubo_absolute_url('brief.php') . '?token=' . $token;
        }
    } elseif ($intent === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM client_briefs WHERE id = ?');
        $stmt->execute([$id]);
        $notice = 'Registro eliminado.';
    }
}

$clients = $pdo->query('SELECT * FROM client_briefs ORDER BY created_at DESC')->fetchAll();

$statusLabels = ['pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'completado' => 'Completado'];

$pageTitle = 'Panel admin — Briefs de clientes | NINUBO';
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
  <div class="container">
    <h1>Briefs de clientes</h1>
    <p style="color:var(--color-text-muted);margin:0.5rem 0 1.75rem;">Crea un enlace único por cliente y da seguimiento a quién ya completó su información.</p>

    <?php if ($notice !== ''): ?>
      <div class="form-alert form-alert--error is-visible" style="margin-bottom:1.5rem;"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <?php if ($newLink !== ''): ?>
      <div class="form-alert form-alert--success is-visible" style="margin-bottom:1.5rem;">
        Enlace creado. Cópialo y compártelo con el cliente:<br>
        <strong id="freshLink" style="word-break:break-all;"><?= htmlspecialchars($newLink) ?></strong>
        <button type="button" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;" data-copy="<?= htmlspecialchars($newLink) ?>">Copiar enlace</button>
      </div>
    <?php endif; ?>

    <div class="admin-card">
      <h2 style="margin-bottom:1rem;">Nuevo cliente</h2>
      <form method="post" class="admin-create-form">
        <input type="hidden" name="intent" value="create">
        <?= ninubo_admin_csrf_field() ?>
        <div class="form-row">
          <label for="client_label">Nombre para identificarlo</label>
          <input type="text" id="client_label" name="client_label" placeholder="Ej. Roofing ABC — McAllen" required>
        </div>
        <button type="submit" class="btn btn-primary">Generar enlace</button>
      </form>
    </div>

    <div class="admin-card">
      <h2 style="margin-bottom:1rem;">Clientes (<?= count($clients) ?>)</h2>
      <?php if (!$clients): ?>
        <p style="color:var(--color-text-muted);">Todavía no has creado ningún enlace.</p>
      <?php else: ?>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Idioma</th>
                <th>Creado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clients as $c):
                $link = ninubo_absolute_url('brief.php', 'es') . '?token=' . $c['token'];
              ?>
                <tr>
                  <td><?= htmlspecialchars($c['client_label']) ?></td>
                  <td><span class="admin-badge admin-badge--<?= htmlspecialchars($c['status']) ?>"><?= htmlspecialchars($statusLabels[$c['status']] ?? $c['status']) ?></span></td>
                  <td><?= $c['lang'] === 'en' ? '🇺🇸 EN' : '🇲🇽 ES' ?></td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['created_at']))) ?></td>
                  <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['updated_at']))) ?></td>
                  <td style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <a class="btn btn-ghost btn-sm" href="<?= ninubo_url('admin/brief-ver.php') ?>?id=<?= (int)$c['id'] ?>">Ver</a>
                    <button type="button" class="admin-link-copy" data-copy="<?= htmlspecialchars($link) ?>">Copiar link</button>
                    <form method="post" onsubmit="return confirm('¿Eliminar este cliente y sus respuestas? Esta acción no se puede deshacer.');" style="display:inline;">
                      <input type="hidden" name="intent" value="delete">
                      <?= ninubo_admin_csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button type="submit" class="btn btn-ghost btn-sm">Eliminar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
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
