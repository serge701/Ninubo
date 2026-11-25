<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Migrator;
use Crm\Core\Session;

final class SystemController extends Controller
{
    /** Applies pending database migrations (button in the admin banner). */
    public function migrate(): void
    {
        try {
            $done = Migrator::migrate();
            $this->success($done ? 'Database updated: ' . implode(', ', $done) : 'The database was already up to date.');
        } catch (\Throwable $e) {
            error_log('[CRM] ' . $e->getMessage());
            Session::flash('danger', 'The database update failed: ' . $e->getMessage());
        }
        $this->redirect('/');
    }
}
