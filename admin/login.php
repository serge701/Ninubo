<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin-auth.php';

if (ninubo_admin_is_logged_in()) {
    header('Location: ' . ninubo_url('admin/index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lockout = ninubo_admin_lockout_remaining();
    if ($lockout > 0) {
        $error = 'Demasiados intentos fallidos. Intenta de nuevo en ' . max(1, (int)ceil($lockout / 60)) . ' min.';
    } elseif (!ninubo_admin_csrf_valid()) {
        $error = 'La sesión del formulario expiró. Vuelve a intentarlo.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        if (ninubo_admin_login($password)) {
            header('Location: ' . ninubo_url('admin/index.php', 'es'));
            exit;
        }
        sleep(1); // Freno adicional contra fuerza bruta.
        $remaining = ninubo_admin_lockout_remaining();
        $error = $remaining > 0
            ? 'Demasiados intentos fallidos. Intenta de nuevo en ' . max(1, (int)ceil($remaining / 60)) . ' min.'
            : 'Contraseña incorrecta.';
    }
}

$pageTitle = 'Panel admin | NINUBO';
$pageDesc = 'Acceso al panel administrativo de NINUBO.';
$pageNoindex = true;
require __DIR__ . '/../includes/head.php';
?>
<main class="brief-main">
  <div class="container">
    <div class="admin-card admin-login-card">
      <h1>Panel admin</h1>
      <p style="color:var(--color-text-muted);margin:0.5rem 0 1.25rem;">Acceso a los briefs de clientes.</p>
      <?php if ($error !== ''): ?>
        <div class="form-alert form-alert--error is-visible"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" class="contact-form">
        <?= ninubo_admin_csrf_field() ?>
        <div class="form-row">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" required autofocus autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-full">Entrar</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>
