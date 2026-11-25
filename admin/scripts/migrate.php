<?php
/**
 * Applies pending CRM database migrations from the command line:
 *   php admin/scripts/migrate.php            apply
 *   php admin/scripts/migrate.php --status   list pending only
 * (Admins can also apply them from the banner inside the CRM.)
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

use Crm\Core\Migrator;

$pending = Migrator::pending();
if (in_array('--status', $argv, true)) {
    echo $pending ? "Pending:\n  " . implode("\n  ", $pending) . "\n" : "Up to date.\n";
    exit(0);
}

try {
    $done = Migrator::migrate();
    echo $done ? "Applied:\n  " . implode("\n  ", $done) . "\n" : "Up to date.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
