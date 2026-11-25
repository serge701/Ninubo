<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Database;
use Crm\Core\Settings;
use Crm\Models\Brief;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $settings = Settings::all();
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'briefCounts' => Brief::counts(),
            'recentBriefs' => array_slice(Brief::all(), 0, 6),
            'userCount' => (int)Database::value('SELECT COUNT(*) FROM users WHERE is_active = 1'),
            'businessConfigured' => $settings['business_address1'] !== '' && $settings['business_phone'] !== '',
        ]);
    }
}
