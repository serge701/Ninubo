<?php
declare(strict_types=1);

// Shared site config (NINUBO_BASE, NINUBO_SECRET, ninubo_url(), ...) and the PDO connection.
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
// Website-brief questions + profiles (shared with the public brief.php).
require_once dirname(__DIR__, 2) . '/includes/brief-schema.php';
require_once dirname(__DIR__, 2) . '/includes/brief-helpers.php';

/** Absolute path of admin/. */
define('CRM_ROOT', dirname(__DIR__));
/** URL prefix of the CRM, e.g. "/ninubo/admin" locally or "/admin" in production. */
define('CRM_BASE', NINUBO_BASE . '/admin');

// The business operates in the Rio Grande Valley (Central Time).
date_default_timezone_set('America/Chicago');

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'Crm\\')) {
        return;
    }
    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/helpers.php';
