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
use Crm\Models\Stats;
use Crm\Models\Subscription;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $me = $this->user();
        $settings = Settings::all();
        $hour = (int)date('G');

        $this->view('dashboard/index', [
            'title' => ($hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening')) . ', ' . explode(' ', $me['name'])[0],
            'pretitle' => date('l, F j'),

            // KPIs
            'thisMonth' => Stats::month(0),
            'lastMonth' => Stats::month(-1),
            'monthly' => Stats::monthly(12),
            'invoiceSummary' => Invoice::summary(),
            'mrr' => Subscription::mrr(),
            'activePlans' => Stats::activePlans(),

            // Charts
            'salesByRep' => Stats::salesByRep(),
            'packagesSold' => Stats::packagesSold(),
            'aging' => Stats::aging(),
            'clientCounts' => Client::counts(),
            'conversion' => Stats::conversion(90),
            'newClients' => Stats::newClientsThisMonth(),

            // Work lists
            'tasks' => Activity::openTasks((int)$me['id'], 8),
            'openTaskCount' => Activity::openTaskCount((int)$me['id']),
            'overdue' => Activity::overdueCount((int)$me['id']),
            'recurringDue' => Subscription::dueWithin(14),
            'contractsPending' => array_merge(Contract::search('client_signed'), Contract::search('sent')),
            'briefCounts' => Brief::counts(),
            'recentClients' => array_slice(Client::search([], 1)['rows'], 0, 5),

            // Setup checklist (admins)
            'userCount' => (int)Database::value('SELECT COUNT(*) FROM users WHERE is_active = 1'),
            'businessConfigured' => $settings['business_address1'] !== '' && $settings['business_phone'] !== '',
            'catalogPriced' => !(int)Database::value('SELECT COUNT(*) FROM packages WHERE is_active = 1 AND price <= 0'),
        ]);
    }
}
