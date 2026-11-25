<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin-auth.php';

// Solo POST con token CSRF; un GET (p.ej. un <img> en otro sitio) no cierra la sesión.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ninubo_admin_csrf_valid()) {
    ninubo_admin_logout();
    header('Location: ' . ninubo_url('admin/login.php', 'es'));
    exit;
}

header('Location: ' . ninubo_url('admin/index.php', 'es'));
exit;
