<?php
/**
 * Ninubo CRM — front controller. Every /admin/* request that isn't a real file
 * (see admin/.htaccess) lands here. App code lives in admin/app/ (not web-accessible).
 */
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

\Crm\Core\App::run();
