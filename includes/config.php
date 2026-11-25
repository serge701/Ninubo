<?php
require_once __DIR__ . '/i18n.php';

$ninuboLanguage = $_GET['lang'] ?? 'es';
define('NINUBO_LANG', $ninuboLanguage === 'en' ? 'en' : 'es');
// En XAMPP el sitio se sirve desde http://localhost/ninubo.
// Cambiar a '' cuando el sitio esté en la raíz de ninubo.com.
define('NINUBO_BASE', '/ninubo');

// Set this in Apache or the hosting control panel to enable Google Analytics.
// Example: G-XXXXXXXXXX. Leave blank in local development and until consent is configured.
define('NINUBO_GA_MEASUREMENT_ID', getenv('NINUBO_GA_MEASUREMENT_ID') ?: '');

// Clave secreta para firmar tokens anti-spam — cambia este valor en producción
define('NINUBO_SECRET', 'REMOVED-SEE-CONFIG-LOCAL');

// Contraseña del panel admin (brief de clientes). Hash de "[removed]" —
// cámbiala antes de producción generando un nuevo hash con password_hash().
define('NINUBO_ADMIN_PASSWORD_HASH', getenv('NINUBO_ADMIN_PASSWORD_HASH') ?: 'REMOVED-SEE-CONFIG-LOCAL');

function ninubo_url(string $path = '', ?string $language = null): string {
    $url = NINUBO_BASE . ($path ? '/' . ltrim($path, '/') : '');
    $url = $url === '' ? '/' : $url;
    $language = $language ?? NINUBO_LANG;
    return $language === 'en' ? $url . '?lang=en' : $url;
}

function ninubo_language_url(string $language): string {
    $page = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    return ninubo_url($page === 'index.php' ? '' : $page, $language);
}

function ninubo_absolute_url(string $path = '', ?string $language = null): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isLocal = $host === 'localhost' || str_starts_with($host, '127.0.0.1');
    $scheme = $isLocal ? 'http' : 'https';
    return $scheme . '://' . $host . ninubo_url($path, $language);
}

function ninubo_generate_token(): string {
    $ts = time();
    $hash = hash_hmac('sha256', (string)$ts, NINUBO_SECRET);
    return $ts . ':' . $hash;
}

function ninubo_validate_token(string $token, int $min_seconds = 3, int $max_seconds = 86400): bool {
    $parts = explode(':', $token, 2);
    if (count($parts) !== 2) return false;
    [$ts, $hash] = $parts;
    if (!ctype_digit($ts)) return false;
    $expected = hash_hmac('sha256', $ts, NINUBO_SECRET);
    if (!hash_equals($expected, $hash)) return false;
    $elapsed = time() - (int)$ts;
    return $elapsed >= $min_seconds && $elapsed <= $max_seconds;
}

/**
 * ¿Debe pasarse esta página por ninubo_translate_html()? Solo las páginas de
 * marketing. Se excluyen:
 *  - brief.php: ya es bilingüe por sí mismo (brief-schema.php + $t()) y contiene
 *    respuestas escritas por el cliente; strtr() las "traduciría" (p.ej.
 *    "Servicios" -> "Services") y el autoguardado las guardaría alteradas.
 *  - admin/ y api/: muestran o devuelven datos de clientes, siempre en español.
 */
function ninubo_should_translate_page(): bool {
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $root = str_replace('\\', '/', dirname(__DIR__));
    $relative = ltrim(str_starts_with($script, $root) ? substr($script, strlen($root)) : basename($script), '/');

    if ($relative === 'brief.php') {
        return false;
    }
    foreach (['admin/', 'api/'] as $prefix) {
        if (str_starts_with($relative, $prefix)) {
            return false;
        }
    }
    return true;
}

if (NINUBO_LANG === 'en' && PHP_SAPI !== 'cli' && ninubo_should_translate_page()) {
    ob_start('ninubo_translate_html');
}
