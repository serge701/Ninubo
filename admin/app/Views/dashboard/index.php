<?php
use Crm\Core\View;
use Crm\Models\Client;
use Crm\Models\Contract;
use Crm\Models\Invoice;

$me = auth_user();
$last6 = array_slice($monthly, -6);
$sparkLabels = array_column($last6, 'label');
$setupDone = $businessConfigured && $catalogPriced && $userCount > 1;

/** Delta vs last month: arrow icon + label (never color alone). */
$delta = function (float $now, float $before): string {
    $label = pct_change($now, $before);
    if ($label === null) {
        return '<span class="text-secondary">No data last month</span>';
    }
    $up = $now >= $before;
    return '<span class="' . ($up ? 'text-green' : 'text-red') . ' fw-medium"><i class="ti ti-trending-' . ($up ? 'up' : 'down') . '"></i> ' . e($label) . '</span> <span class="text-secondary">vs last month</span>';
};

// Pipeline composition
$pipeTotal = array_sum($clientCounts);
$pipe = [
    ['lead', 'Leads', 'var(--viz-1)'],
    ['active', 'Active', 'var(--viz-3)'],
    ['inactive', 'Inactive', 'var(--viz-muted)'],
];
$agingTotal = array_sum(array_column($aging, 'amount'));
$seq = ['var(--seq-1)', 'var(--seq-2)', 'var(--seq-3)', 'var(--seq-4)'];
?>
<?php View::start('actions'); ?>
<a href="<?= url('/invoices') ?>" class="btn"><i class="ti ti-file-invoice me-1"></i>Invoices</a>
<a href="<?= url('/clients/new') ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New client</a>
<?php View::end(); ?>

<?php if (is_admin() && !$setupDone): ?>
<div class="card card-sm mb-3 crm-setup">
  <div class="card-body d-flex flex-wrap align-items-center gap-3">
    <span class="avatar bg-primary-lt"><i class="ti ti-rocket"></i></span>
    <div class="fw-medium me-2">Finish setting up the CRM</div>
    <a href="<?= url('/settings') ?>" class="crm-check<?= $businessConfigured ? ' is-done' : '' ?>"><i class="ti ti-<?= $businessConfigured ? 'circle-check' : 'circle' ?>"></i>Business details</a>
    <a href="<?= url('/catalog') ?>" class="crm-check<?= $catalogPriced ? ' is-done' : '' ?>"><i class="ti ti-<?= $catalogPriced ? 'circle-check' : 'circle' ?>"></i>Catalog prices</a>
    <a href="<?= url('/users/new') ?>" class="crm-check<?= $userCount > 1 ? ' is-done' : '' ?>"><i class="ti ti-<?= $userCount > 1 ? 'circle-check' : 'circle' ?>"></i>Sales team</a>
  </div>
</div>
<?php endif; ?>

<!-- KPI tiles -->
<div class="row row-deck row-cards mb-3">
  <div class="col-sm-6 col-xl-3">
    <a class="card crm-kpi card-link" href="<?= url('/invoices') ?>">
      <div class="card-body">
        <div class="crm-kpi-label"><i class="ti ti-receipt"></i>Sales this month</div>
        <div class="crm-kpi-value"><?= e(money($thisMonth['sales'])) ?></div>
        <div class="crm-kpi-sub"><?= $delta($thisMonth['sales'], $lastMonth['sales']) ?></div>
        <div class="crm-kpi-bottom">
          <div class="crm-kpi-foot"><?= (int)$thisMonth['sales_count'] ?> invoice<?= $thisMonth['sales_count'] === 1 ? '' : 's' ?> issued</div>
          <?= View::partial('partials/viz_spark', ['values' => array_column($last6, 'invoiced'), 'labels' => $sparkLabels, 'color' => 'var(--viz-2)', 'name' => 'Invoiced']) ?>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="card crm-kpi card-link" href="<?= url('/invoices?status=paid') ?>">
      <div class="card-body">
        <div class="crm-kpi-label"><i class="ti ti-cash"></i>Collected this month</div>
        <div class="crm-kpi-value"><?= e(money($thisMonth['collected'])) ?></div>
        <div class="crm-kpi-sub"><?= $delta($thisMonth['collected'], $lastMonth['collected']) ?></div>
        <div class="crm-kpi-bottom">
          <div class="crm-kpi-foot">Payments received</div>
          <?= View::partial('partials/viz_spark', ['values' => array_column($last6, 'collected'), 'labels' => $sparkLabels, 'color' => 'var(--viz-1)', 'name' => 'Collected']) ?>
        </div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="card crm-kpi card-link" href="<?= url('/invoices?status=' . ((int)$invoiceSummary['overdue_count'] ? 'overdue' : 'open')) ?>">
      <div class="card-body">
        <div class="crm-kpi-label"><i class="ti ti-clock-dollar"></i>Outstanding</div>
        <div class="crm-kpi-value"><?= e(money($invoiceSummary['outstanding'])) ?></div>
        <div class="crm-kpi-sub">
          <?php if ((int)$invoiceSummary['overdue_count']): ?>
            <span class="text-red fw-medium"><i class="ti ti-alert-triangle"></i> <?= e(money($invoiceSummary['overdue_amount'])) ?> overdue</span>
          <?php else: ?>
            <span class="text-green fw-medium"><i class="ti ti-circle-check"></i> Nothing overdue</span>
          <?php endif; ?>
        </div>
        <div class="crm-kpi-foot"><?= (int)$invoiceSummary['open_count'] ?> open invoice<?= (int)$invoiceSummary['open_count'] === 1 ? '' : 's' ?></div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a class="card crm-kpi card-link" href="#recurring-due">
      <div class="card-body">
        <div class="crm-kpi-label"><i class="ti ti-repeat"></i>Monthly recurring</div>
        <div class="crm-kpi-value"><?= e(money($mrr)) ?><span class="crm-kpi-unit">/mo</span></div>
        <div class="crm-kpi-sub"><span class="text-secondary"><?= e(money($mrr * 12)) ?> a year</span></div>
        <div class="crm-kpi-foot"><?= (int)$activePlans ?> active care plan<?= $activePlans === 1 ? '' : 's' ?> · renewals counted monthly</div>
      </div>
    </a>
  </div>
</div>

<!-- Revenue trend + pipeline -->
<div class="row row-cards mb-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">
        <div>
          <h3 class="card-title">Invoiced vs collected</h3>
          <div class="card-subtitle">Last 12 months · including sales tax</div>
        </div>
      </div>
      <div class="card-body">
        <?= View::partial('partials/viz_monthly', ['months' => $monthly]) ?>
      </div>
    </div>

    <div class="row row-cards">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header">
            <div><h3 class="card-title">Sales by salesperson</h3><div class="card-subtitle"><?= date('Y') ?> · invoices issued</div></div>
          </div>
          <div class="card-body">
            <?php
            $rows = array_map(fn($r) => ['label' => $r['name'], 'value' => (float)$r['total'], 'display' => money_short($r['total']),
                'sub' => '· ' . (int)$r['n'] . ' inv.', 'tip' => [['var(--viz-1)', 'Sales', money($r['total'])], ['var(--viz-1)', 'Invoices', (string)$r['n']]]], $salesByRep);
            echo View::partial('partials/viz_hbars', ['rows' => $rows, 'empty' => 'No invoices issued this year yet.']);
            ?>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header">
            <div><h3 class="card-title">Website packages sold</h3><div class="card-subtitle"><?= date('Y') ?> · number of projects</div></div>
          </div>
          <div class="card-body">
            <?php
            $rows = array_map(fn($p) => ['label' => $p['name'], 'value' => (float)$p['n'], 'display' => (string)(int)$p['n'],
                'sub' => (float)$p['revenue'] > 0 ? '· ' . money_short($p['revenue']) : '',
                'tip' => [['var(--viz-1)', 'Projects', (string)$p['n']], ['var(--viz-1)', 'Revenue (before tax)', money($p['revenue'])]]], $packagesSold);
            echo View::partial('partials/viz_hbars', ['rows' => $rows, 'empty' => 'No website packages sold this year yet.']);
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">
        <div>
          <h3 class="card-title">Client pipeline</h3>
          <div class="card-subtitle"><?= (int)$pipeTotal ?> client<?= $pipeTotal === 1 ? '' : 's' ?> · <?= (int)$newClients ?> new this month</div>
        </div>
      </div>
      <div class="card-body crm-viz">
        <?php if ($pipeTotal === 0): ?>
          <div class="crm-viz-empty">No clients yet.</div>
        <?php else: ?>
        <div class="crm-stack" role="img" aria-label="Clients by status">
          <?php foreach ($pipe as [$key, $label, $color]): if (!$clientCounts[$key]) continue; ?>
          <div class="crm-stack-seg" style="flex: <?= (int)$clientCounts[$key] ?> 1 0; background: <?= $color ?>"
               data-tip-title="<?= e($label) ?>" data-tip='<?= e(json_encode([[$color, 'Clients', (string)$clientCounts[$key]], [$color, 'Share', round($clientCounts[$key] / $pipeTotal * 100) . '%']])) ?>'></div>
          <?php endforeach; ?>
        </div>
        <div class="crm-stack-legend">
          <?php foreach ($pipe as [$key, $label, $color]): ?>
          <a href="<?= url('/clients?status=' . $key) ?>" class="crm-stack-item">
            <span class="crm-swatch" style="background: <?= $color ?>"></span>
            <span class="text-secondary"><?= e($label) ?></span>
            <strong><?= (int)$clientCounts[$key] ?></strong>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="crm-conversion">
          <div>
            <div class="crm-conversion-value"><?= $conversion['rate'] === null ? '—' : number_format($conversion['rate'], 0) . '%' ?></div>
            <div class="small text-secondary">Lead conversion · 90 days</div>
          </div>
          <div class="small text-secondary text-end"><?= (int)$conversion['won'] ?> of <?= (int)$conversion['created'] ?> new clients<br>became active</div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <div>
          <h3 class="card-title">Receivables aging</h3>
          <div class="card-subtitle"><?= e(money($agingTotal)) ?> open balance</div>
        </div>
      </div>
      <div class="card-body">
        <?php
        $rows = [];
        foreach ($aging as $i => $a) {
            $rows[] = ['label' => $a['label'], 'value' => $a['amount'], 'display' => money($a['amount']),
                       'sub' => $a['count'] ? '· ' . $a['count'] : '', 'color' => $seq[$i],
                       'tip' => [[$seq[$i], 'Balance', money($a['amount'])], [$seq[$i], 'Invoices', (string)$a['count']]]];
        }
        echo View::partial('partials/viz_hbars', ['rows' => $rows, 'empty' => 'No open balances — everything is paid.']);
        ?>
      </div>
    </div>
    <div class="card mb-3">
      <div class="card-header"><h3 class="card-title">Contracts in progress</h3></div>
      <?php if (!$contractsPending): ?>
      <div class="card-body text-secondary">No contracts waiting for signatures.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach (array_slice($contractsPending, 0, 6) as $k): ?>
        <a href="<?= url('/contracts/' . (int)$k['id']) ?>" class="list-group-item list-group-item-action">
          <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm flex-shrink-0 bg-<?= $k['status'] === 'client_signed' ? 'green' : 'blue' ?>-lt" title="<?= e(Contract::STATUSES[$k['status']]) ?>">
              <i class="ti ti-<?= $k['status'] === 'client_signed' ? 'signature' : 'send' ?>"></i>
            </span>
            <div class="min-w-0">
              <div class="fw-medium text-truncate"><?= e($k['number']) ?> · <?= e(Client::displayName($k)) ?></div>
              <div class="small text-secondary"><?= $k['status'] === 'client_signed' ? 'Signed by the client — ready to countersign' : 'Waiting for the client to sign · sent ' . e(time_ago($k['sent_at'])) ?></div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="card mb-3">
      <div class="card-body d-flex align-items-center gap-3">
        <span class="avatar bg-yellow-lt"><i class="ti ti-forms"></i></span>
        <div class="flex-fill">
          <div class="fw-medium">Website briefs</div>
          <div class="small text-secondary"><?= (int)$briefCounts['en_progreso'] ?> in progress · <?= (int)$briefCounts['pendiente'] ?> not started · <?= (int)$briefCounts['completado'] ?> completed</div>
        </div>
        <a href="<?= url('/briefs?status=en_progreso') ?>" class="btn btn-sm">Open</a>
      </div>
    </div>
  </div>
</div>

<!-- Work lists -->
<div class="row row-cards">
  <div class="col-lg-7">
    <div class="card mb-3" id="follow-ups">
      <div class="card-header">
        <h3 class="card-title">My follow-ups</h3>
        <?php if ($overdue): ?><span class="badge bg-red-lt ms-2"><i class="ti ti-alert-triangle me-1"></i><?= (int)$overdue ?> overdue</span><?php endif; ?>
        <span class="ms-auto text-secondary small"><?= (int)$openTaskCount ?> open</span>
      </div>
      <?php if (!$tasks): ?>
      <div class="card-body text-secondary">Nothing pending. Add follow-ups from a client's activity timeline.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($tasks as $t): $late = $t['due_at'] !== null && strtotime($t['due_at']) < time(); ?>
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
                <?php if ($t['due_at']): ?> · <span class="<?= $late ? 'text-red fw-medium' : '' ?>"><?= $late ? '<i class="ti ti-alert-triangle"></i> ' : '' ?>Due <?= e(fmt_datetime($t['due_at'])) ?></span><?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="card mb-3" id="recurring-due">
      <div class="card-header"><h3 class="card-title">Recurring invoices due · next 14 days</h3></div>
      <?php if (!$recurringDue): ?>
      <div class="card-body text-secondary">Nothing to bill soon.</div>
      <?php else: ?>
      <div class="list-group list-group-flush">
        <?php foreach ($recurringDue as $r): $late = $r['next_invoice_date'] < date('Y-m-d'); ?>
        <a href="<?= url('/clients/' . (int)$r['client_id']) ?>#recurring" class="list-group-item list-group-item-action">
          <div class="d-flex justify-content-between gap-2">
            <div>
              <div class="fw-medium"><?= e(Client::displayName($r)) ?></div>
              <div class="small text-secondary"><?= e($r['description']) ?> · <?= e(money($r['unit_price'])) ?>/<?= $r['billing'] === 'yearly' ? 'yr' : 'mo' ?></div>
            </div>
            <div class="small text-nowrap <?= $late ? 'text-red fw-medium' : 'text-secondary' ?>"><?= $late ? '<i class="ti ti-alert-triangle"></i> Late since ' : 'Due ' ?><?= e(fmt_date($r['next_invoice_date'])) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">

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

<script src="<?= asset('app/viz.js') ?>"></script>
