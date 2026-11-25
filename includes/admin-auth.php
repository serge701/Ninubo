<?php
/**
 * Autenticación mínima del panel admin (una sola contraseña, sin usuarios).
 * Debe incluirse después de includes/config.php.
 *
 * Protecciones:
 *  - Cookie de sesión HttpOnly, SameSite=Lax, Secure en HTTPS y limitada a /admin/.
 *  - Sesión expira tras NINUBO_ADMIN_IDLE_SECONDS sin actividad.
 *  - Token CSRF obligatorio en todo POST del panel (login incluido).
 *  - Bloqueo por IP tras varios intentos de contraseña fallidos.
 */

const NINUBO_ADMIN_IDLE_SECONDS = 7200;      // 2 h sin actividad -> cerrar sesión
const NINUBO_ADMIN_MAX_ATTEMPTS = 5;         // intentos fallidos permitidos...
const NINUBO_ADMIN_ATTEMPT_WINDOW = 900;     // ...dentro de esta ventana (15 min)

function ninubo_admin_is_https(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    return strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function ninubo_admin_start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('ninubo_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => (NINUBO_BASE === '' ? '' : NINUBO_BASE) . '/admin/',
        'secure' => ninubo_admin_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Expiración por inactividad.
    $now = time();
    if (!empty($_SESSION['ninubo_admin_ok'])
        && isset($_SESSION['ninubo_admin_last'])
        && $now - (int)$_SESSION['ninubo_admin_last'] > NINUBO_ADMIN_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['ninubo_admin_last'] = $now;
}

function ninubo_admin_is_logged_in(): bool {
    ninubo_admin_start_session();
    return !empty($_SESSION['ninubo_admin_ok']);
}

function ninubo_admin_require_login(): void {
    ninubo_admin_start_session();
    if (empty($_SESSION['ninubo_admin_ok'])) {
        header('Location: ' . ninubo_url('admin/login.php', 'es'));
        exit;
    }
}

/* ---------------------------------------------------------------- CSRF */

function ninubo_admin_csrf_token(): string {
    ninubo_admin_start_session();
    if (empty($_SESSION['ninubo_admin_csrf'])) {
        $_SESSION['ninubo_admin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['ninubo_admin_csrf'];
}

function ninubo_admin_csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(ninubo_admin_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function ninubo_admin_csrf_valid(): bool {
    ninubo_admin_start_session();
    $sent = (string)($_POST['_csrf'] ?? '');
    $expected = (string)($_SESSION['ninubo_admin_csrf'] ?? '');
    return $expected !== '' && $sent !== '' && hash_equals($expected, $sent);
}

/** Para POSTs de páginas ya autenticadas: corta con 403 si el token no coincide. */
function ninubo_admin_require_csrf(): void {
    if (!ninubo_admin_csrf_valid()) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Solicitud no válida o sesión expirada. Regresa al panel y vuelve a intentarlo.';
        exit;
    }
}

/* ------------------------------------------- Límite de intentos de login */

function ninubo_admin_attempts_file(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR
        . 'ninubo_login_' . hash_hmac('sha256', $ip, NINUBO_SECRET) . '.json';
}

/** Timestamps de intentos fallidos recientes para esta IP. */
function ninubo_admin_recent_failures(): array {
    $file = ninubo_admin_attempts_file();
    if (!is_file($file)) {
        return [];
    }
    $list = json_decode((string)@file_get_contents($file), true);
    if (!is_array($list)) {
        return [];
    }
    $cutoff = time() - NINUBO_ADMIN_ATTEMPT_WINDOW;
    return array_values(array_filter($list, fn($ts) => is_int($ts) && $ts > $cutoff));
}

/** Segundos que faltan para poder volver a intentar (0 = no bloqueado). */
function ninubo_admin_lockout_remaining(): int {
    $failures = ninubo_admin_recent_failures();
    if (count($failures) < NINUBO_ADMIN_MAX_ATTEMPTS) {
        return 0;
    }
    $oldestRelevant = $failures[count($failures) - NINUBO_ADMIN_MAX_ATTEMPTS];
    return max(0, $oldestRelevant + NINUBO_ADMIN_ATTEMPT_WINDOW - time());
}

function ninubo_admin_record_failure(): void {
    $failures = ninubo_admin_recent_failures();
    $failures[] = time();
    @file_put_contents(ninubo_admin_attempts_file(), json_encode($failures), LOCK_EX);
}

function ninubo_admin_clear_failures(): void {
    @unlink(ninubo_admin_attempts_file());
}

/* ------------------------------------------------------- Login / logout */

function ninubo_admin_login(string $password): bool {
    ninubo_admin_start_session();
    if (password_verify($password, NINUBO_ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['ninubo_admin_ok'] = true;
        $_SESSION['ninubo_admin_csrf'] = bin2hex(random_bytes(32)); // token nuevo tras login
        ninubo_admin_clear_failures();
        return true;
    }
    ninubo_admin_record_failure();
    return false;
}

function ninubo_admin_logout(): void {
    ninubo_admin_start_session();
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $params['path'],
        'secure' => $params['secure'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_destroy();
}

/** Botón "Cerrar sesión" como POST con CSRF (un GET permitiría cerrar sesión desde otro sitio). */
function ninubo_admin_logout_button(): string {
    return '<form method="post" action="' . htmlspecialchars(ninubo_url('admin/logout.php', 'es'), ENT_QUOTES, 'UTF-8') . '" style="margin:0;">'
        . ninubo_admin_csrf_field()
        . '<button type="submit" class="btn btn-ghost btn-sm">Cerrar sesión</button></form>';
}
