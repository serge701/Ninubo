<?php
declare(strict_types=1);

namespace Crm\Core;

final class App
{
    public static function isLocal(): bool
    {
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        return $host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_starts_with($host, 'localhost:');
    }

    public static function run(): void
    {
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: same-origin');
        header('Cache-Control: no-store, private');

        error_reporting(E_ALL);
        ini_set('display_errors', self::isLocal() ? '1' : '0');
        ini_set('log_errors', '1');
        @mkdir(CRM_ROOT . '/storage/logs', 0775, true);
        ini_set('error_log', CRM_ROOT . '/storage/logs/php-error.log');

        Session::start();

        $router = new Router();
        require CRM_ROOT . '/app/routes.php';

        $uri = self::currentPath();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            self::dispatch($router, $method, $uri);
        } catch (\Throwable $e) {
            error_log('[CRM] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $detail = self::isLocal() ? $e->getMessage() : null;
            self::abort(500, 'Something went wrong', 'The error was logged. Please try again or contact the administrator.', $detail);
        }
    }

    /** Request path relative to the CRM root, e.g. "/clients/5". */
    public static function currentPath(): string
    {
        $path = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        if (CRM_BASE !== '' && stripos($path, CRM_BASE) === 0) {
            $path = substr($path, strlen(CRM_BASE));
        }
        $path = '/' . trim($path, '/');
        return $path === '/index.php' ? '/' : $path;
    }

    private static function dispatch(Router $router, string $method, string $uri): void
    {
        // First run: no user accounts yet -> one-time setup screen.
        $needsSetup = !Database::tableExists('users') || (int)Database::value('SELECT COUNT(*) FROM users') === 0;
        if ($needsSetup && $uri !== '/setup') {
            header('Location: ' . url('/setup'));
            exit;
        }
        if (!$needsSetup && $uri === '/setup') {
            header('Location: ' . url('/'));
            exit;
        }

        $match = $router->match($method, $uri);
        if ($match === null) {
            if ($router->pathExists($uri)) {
                self::abort(405, 'Method not allowed', 'This action is not available that way.');
            }
            self::abort(404, 'Page not found', 'The page you are looking for does not exist or was moved.');
        }

        $options = $match['options'];
        if (empty($options['public'])) {
            $user = Auth::user();
            if ($user === null) {
                if ($method === 'GET') {
                    Session::set('intended', $uri . (empty($_GET) ? '' : '?' . http_build_query($_GET)));
                }
                header('Location: ' . url('/login'));
                exit;
            }
            if ((int)$user['must_change_password'] && !in_array($uri, ['/profile/password', '/logout'], true)) {
                Session::flash('warning', 'Please choose a new password before continuing.');
                header('Location: ' . url('/profile/password'));
                exit;
            }
            if (($options['role'] ?? null) === 'admin' && $user['role'] !== 'admin') {
                self::abort(403, 'Not allowed', 'Only administrators can open this page.');
            }
        }

        if ($method === 'POST' && !Csrf::verify($_POST['_csrf'] ?? null)) {
            self::abort(403, 'Form expired', 'The form expired or was submitted from another site. Go back, reload the page and try again.');
        }

        [$class, $action] = $match['handler'];
        (new $class())->$action(...array_values($match['params']));
    }

    public static function abort(int $code, string $title, string $message, ?string $detail = null): never
    {
        http_response_code($code);
        try {
            $layout = Auth::user() !== null ? 'layouts/main' : 'layouts/auth';
        } catch (\Throwable $e) {
            $layout = 'layouts/auth'; // e.g. database down
        }
        try {
            View::render('errors/error', compact('code', 'title', 'message', 'detail'), $layout);
        } catch (\Throwable $e) {
            echo '<h1>' . htmlspecialchars($title) . '</h1><p>' . htmlspecialchars($message) . '</p>';
        }
        exit;
    }
}
