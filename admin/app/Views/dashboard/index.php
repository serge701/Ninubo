<?php
use Crm\Core\View;
use Crm\Models\Client;

$me = auth_user();
$recentClients = array_slice($recentClients, 0, 6);
$setupDone = $businessConfigured && $catalogPriced && $userCount > 1;
?>
<?php View::start('actions'); ?>
<a href="<?= url('/clients/new') ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New client</a>
<?php View::end(); ?>

<div class="row row-deck row-cards mb-3">
  <?php
  $cards = [
      [url('/clients?status=lead'), $clientCounts['lead'], 'Leads', 'blue', 'user-search', $newClients30 . ' new client' . ($newClients30 === 1 ? '' : 's') . ' in 30 days'],
      [url('/clients?status=active'), $clientCounts['active'], 'Active clients', 'green', 'user-check', 'Buying or under contract'],
      ['#follow-ups', $openTaskCount, 'My open follow-ups', $overdue ? 'red' : 'orange', 'calendar-check', $overdue ? $overdue . ' overdue' : 'None overdue'],
      [url('/briefs?status=en_progreso'), $briefCounts['en_progreso'], 'Briefs in progress', 'yellow', 'forms', $briefCounts['completado'] . ' completed'],
  ];
  foreach ($cards as [$href, $value, $label, $color, $icon, $sub]): ?>
  <div class="col-sm-6 col-lg-3">
    <a class="card card-sm card-link" href="<?= e($href) ?>">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-<?= $color ?> text-white avatar"><i class="ti ti-<?= $icon ?>"></i></span></div>
          <div class="col">
            <div class="font-weight-medium"><?= (int)$value ?> · <?= e($label) ?></div>
            <div class="text-secondary small"><?= e($sub) ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="row row-deck row-cards mb-3">
  <?php foreach ([
      [url('/invoices?status=open'), money($invoiceSummary['outstanding']), 'Outstanding', 'blue', 'clock-dollar', (int)$invoiceSummary['open_count'] . ' open invoice(s)'],
      [url('/invoices?status=overdue'), money($invoiceSummary['overdue_amount']), 'Overdue', (int)$invoiceSummary['overdue_count'] ? 'red' : 'secondary', 'alert-triangle', (int)$invoiceSummary['overdue_count'] . ' invoice(s) past due'],
      [url('/invoices?status=paid'), money($invoiceSummary['paid_this_month']), 'Collected this month', 'green', 'cash', date('F Y')],
      ['#recurring-due', money($mrr), 'Monthly recurring', 'purple', 'repeat', 'Active care plans & renewals'],
  ] as [$href, $value, $label, $color, $icon, $sub]): ?>
  <div class="col-sm-6 col-lg-3">
    <a class="card card-sm card-link" href="<?= e($href) ?>">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-<?= $color ?> text-white avatar"><i class="ti ti-<?= $icon ?>"></i></span></div>
          <div class="col"><div class="font-weight-medium"><?= e($value) ?> · <?= e($label) ?></div><div class="text-secondary small"><?= e($sub) ?></div></div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="row row-cards">
  <div class="col-lg-7">
    <div class="card mb-3" id="recurring-due">
      <div class="card-header"><h3 class="card-title">Recurring invoices due in the next 14 days</h3></div>
      <?php if (!$recurringDue): ?>
      <div class="card-body text-secondary">Nothing to bill soon.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($recurringDue as $r): $late = $r['next_invoice_date'] < date('Y-m-d'); ?>
        <a href="<?= url('/clients/' . (int)$r['client_id']) ?>#recurring" class="list-group-item list-group-item-action">
          <div class="d-flex justify-content-between">
            <div>
              <div class="fw-medium"><?= e(Client::displayName($r)) ?></div>
              <div class="small text-secondary"><?= e($r['description']) ?> · <?= e(money($r['unit_price'])) ?>/<?= $r['billing'] === 'yearly' ? 'yr' : 'mo' ?></div>
            </div>
            <div class="small <?= $late ? 'text-red fw-medium' : 'text-secondary' ?>"><?= $late ? 'Late since ' : 'Due ' ?><?= e(fmt_date($r['next_invoice_date'])) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="card mb-3" id="follow-ups">
      <div class="card-header"><h3 class="card-title">My follow-ups</h3></div>
      <?php if (!$tasks): ?>
      <div class="card-body text-secondary">Nothing pending. Add follow-ups from a client's activity timeline.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($tasks as $t):
          $overdueTask = $t['due_at'] !== null && strtotime($t['due_at']) < time(); ?>
        <div class="list-group-item">
          <div class="row align-items-center g-2">
            <div class="col-auto">
              <form method="post" action="<?= url('/activities/' . (int)$t['id'] . '/toggle') ?>" class="m-0">
                <?= csrf_field() ?><input type="hidden" name="back" value="dashboard">
                <button class="btn btn-icon btn-sm btn-ghost-success" title="Mark done"><i class="ti ti-square"></i></button>
              </form>
            </div>
            <div class="col">
              <div><?= e(mb_strimwidth($t['body'], 0, 120, '…')) ?></div>
              <div class="small text-secondary">
                <a href="<?= url('/clients/' . (int)$t['client_id']) ?>#activity" class="text-reset"><?= e(Client::displayName($t)) ?></a>
                <?php if ($t['due_at']): ?> · <span class="<?= $overdueTask ? 'text-red fw-medium' : '' ?>">Due <?= e(fmt_datetime($t['due_at'])) ?></span><?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <?php if (is_admin() && !$setupDone): ?>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Getting the CRM ready</h3></div>
      <div class="card-body">
        <ul class="list-unstyled mb-0 d-grid gap-2">
          <li><i class="ti ti-<?= $businessConfigured ? 'circle-check text-green' : 'circle text-secondary' ?> me-1"></i><a href="<?= url('/settings') ?>">Fill in your business details</a></li>
          <li><i class="ti ti-<?= $catalogPriced ? 'circle-check text-green' : 'circle text-secondary' ?> me-1"></i><a href="<?= url('/catalog') ?>">Set prices in the catalog</a></li>
          <li><i class="ti ti-<?= $userCount > 1 ? 'circle-check text-green' : 'circle text-secondary' ?> me-1"></i><a href="<?= url('/users/new') ?>">Add your sales team</a></li>
        </ul>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($contractsPending): ?>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Contracts in progress</h3></div>
      <div class="list-group list-group-flush">
        <?php foreach (array_slice($contractsPending, 0, 6) as $k): ?>
        <a href="<?= url('/contracts/' . (int)$k['id']) ?>" class="list-group-item list-group-item-action">
          <div class="d-flex justify-content-between align-items-center gap-2">
            <div>
              <div class="fw-medium"><?= e($k['number']) ?> · <?= e(Client::displayName($k)) ?></div>
              <div class="small text-secondary"><?= $k['status'] === 'client_signed' ? 'Signed by the client — ready to countersign' : 'Waiting for the client to sign · sent ' . e(time_ago($k['sent_at'])) ?></div>
            </div>
            <?= status_badge($k['status'], \Crm\Models\Contract::STATUS_COLORS, \Crm\Models\Contract::STATUSES) ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Recently updated clients</h3>
        <div class="card-actions"><a href="<?= url('/clients') ?>" class="btn btn-sm">All clients</a></div>
      </div>
      <?php if (!$recentClients): ?>
      <div class="card-body text-secondary">No clients yet. <a href="<?= url('/clients/new') ?>">Create the first one</a> — every sale starts with a client.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($recentClients as $c): ?>
        <a href="<?= url('/clients/' . (int)$c['id']) ?>" class="list-group-item list-group-item-action">
          <div class="d-flex align-items-center">
            <span class="avatar avatar-sm me-2"><?= e(initials(Client::displayName($c))) ?></span>
            <div class="flex-fill">
              <div class="fw-medium"><?= e(Client::displayName($c)) ?></div>
              <div class="small text-secondary"><?= e(time_ago($c['last_activity_at'] ?? $c['updated_at'])) ?></div>
            </div>
            <?= status_badge($c['status'], Client::STATUS_COLORS, Client::STATUSES) ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
