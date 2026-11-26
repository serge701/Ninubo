<?php
declare(strict_types=1);

namespace Crm\Controllers;

use Crm\Core\Controller;
use Crm\Core\Database;
use Crm\Core\Settings;
use Crm\Models\Activity;
use Crm\Models\Brief;
use Crm\Models\Client;
use Crm\Models\Contract;
use Crm\Models\Invoice;
use Crm\Models\Subscription;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $me = $this->user();
        $settings = Settings::all();
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'clientCounts' => Client::counts(),
            'newClients30' => (int)Database::value('SELECT COUNT(*) FROM clients WHERE created_at >= NOW() - INTERVAL 30 DAY'),
            'briefCounts' => Brief::counts(),
            'tasks' => Activity::openTasks((int)$me['id'], 8),
            'openTaskCount' => Activity::openTaskCount((int)$me['id']),
            'overdue' => Activity::overdueCount((int)$me['id']),
            'recentClients' => Client::search([], 1)['rows'],
            'userCount' => (int)Database::value('SELECT COUNT(*) FROM users WHERE is_active = 1'),
            'businessConfigured' => $settings['business_address1'] !== '' && $settings['business_phone'] !== '',
            'catalogPriced' => !(int)Database::value('SELECT COUNT(*) FROM packages WHERE is_active = 1 AND price <= 0'),
            'invoiceSummary' => Invoice::summary(),
            'recurringDue' => Subscription::dueWithin(14),
            'mrr' => Subscription::mrr(),
            'contractsPending' => array_merge(Contract::search('client_signed'), Contract::search('sent')),
        ]);
    }
}
