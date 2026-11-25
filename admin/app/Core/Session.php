<?php
declare(strict_types=1);

namespace Crm\Core;

/**
 * Hardened session limited to the CRM path: HttpOnly, SameSite=Lax, Secure under HTTPS,
 * strict mode (only server-issued IDs accepted), and a 2-hour idle timeout.
 */
final class Session
{
    public const IDLE_SECONDS = 7200;

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        return strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('ninubo_crm');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => CRM_BASE . '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Flash values set during the previous request are readable now; new ones go to the next request.
        $_SESSION['_flash_now'] = $_SESSION['_flash_next'] ?? [];
        $_SESSION['_flash_next'] = [];

        $now = time();
        $last = (int)($_SESSION['_last'] ?? $now);
        if (isset($_SESSION['user_id']) && $now - $last > self::IDLE_SECONDS) {
            $_SESSION = ['_flash_now' => [], '_flash_next' => []];
            session_regenerate_id(true);
            self::flash('warning', 'Your session expired after inactivity. Please sign in again.');
        }
        $_SESSION['_last'] = $now;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Store a value for the next request. */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash_next'][$key] = $value;
    }

    /** Read a value flashed by the previous request. */
    public static function peekFlash(string $key): mixed
    {
        return $_SESSION['_flash_now'][$key] ?? null;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
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
}
