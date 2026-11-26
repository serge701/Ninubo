<?php
use Crm\Models\Client;
use Crm\Models\Invoice;

$tabs = ['' => 'All', 'open' => 'Open', 'overdue' => 'Overdue', 'draft' => 'Drafts', 'paid' => 'Paid', 'void' => 'Void'];
?>
<div class="row row-deck row-cards mb-3">
  <?php foreach ([
      ['Outstanding', money($summary['outstanding']), (int)$summary['open_count'] . ' open invoice(s)', 'blue', 'clock-dollar', 'open'],
      ['Overdue', money($summary['overdue_amount']), (int)$summary['overdue_count'] . ' invoice(s) past due', (int)$summary['overdue_count'] ? 'red' : 'secondary', 'alert-triangle', 'overdue'],
      ['Collected this month', money($summary['paid_this_month']), date('F Y'), 'green', 'cash', 'paid'],
      ['Drafts', (string)(int)$summary['draft_count'], 'Not issued yet', 'secondary', 'file-pencil', 'draft'],
  ] as [$label, $value, $sub, $color, $icon, $tab]): ?>
  <div class="col-sm-6 col-lg-3">
    <a class="card card-sm card-link" href="<?= url('/invoices?status=' . $tab) ?>">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto"><span class="bg-<?= $color ?> text-white avatar"><i class="ti ti-<?= $icon ?>"></i></span></div>
          <div class="col"><div class="font-weight-medium"><?= e($value) ?></div><div class="text-secondary small"><?= e($label) ?> · <?= e($sub) ?></div></div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-header d-flex flex-wrap gap-2">
    <ul class="nav nav-pills card-header-pills">
      <?php foreach ($tabs as $key => $label): ?>
      <li class="nav-item"><a class="nav-link<?= $filters['status'] === $key ? ' active' : '' ?>" href="<?= url('/invoices') . query_with(['status' => $key]) ?>"><?= e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>
    <form method="get" action="<?= url('/invoices') ?>" class="ms-auto">
      <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
      <div class="input-icon">
        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
        <input type="search" class="form-control form-control-sm" name="q" value="<?= e($filters['q']) ?>" placeholder="Number or client…">
      </div>
    </form>
  </div>
  <?php if (!$invoices): ?>
  <div class="card-body">
    <div class="empty">
      <div class="empty-icon"><i class="ti ti-file-invoice" style="font-size:2.5rem"></i></div>
      <p class="empty-title">No invoices here</p>
      <p class="empty-subtitle text-secondary">Invoices are created from a client: open the client and choose New → Invoice.</p>
      <div class="empty-action"><a href="<?= url('/clients') ?>" class="btn btn-primary"><i class="ti ti-address-book me-1"></i>Go to clients</a></div>
    </div>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead><tr><th>Invoice</th><th>Client</th><th>Issued</th><th>Due</th><th class="text-end">Total</th><th class="text-end">Balance</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($invoices as $i): $st = Invoice::displayStatus($i); ?>
        <tr>
          <td><a href="<?= url('/invoices/' . (int)$i['id']) ?>" class="fw-medium"><?= e(Invoice::displayNumber($i)) ?></a></td>
          <td><a href="<?= url('/clients/' . (int)$i['client_id']) ?>" class="text-reset"><?= e(Client::displayName($i)) ?></a></td>
          <td class="text-secondary"><?= e(fmt_date($i['issue_date'])) ?></td>
          <td class="<?= $st === 'overdue' ? 'text-red fw-medium' : 'text-secondary' ?>"><?= e(fmt_date($i['due_date'])) ?></td>
          <td class="text-end"><?= e(money($i['total'])) ?></td>
          <td class="text-end"><?= in_array($i['status'], ['draft', 'void'], true) ? '<span class="text-secondary">—</span>' : e(money(Invoice::balance($i))) ?></td>
          <td><?= status_badge($st, Invoice::STATUS_COLORS, Invoice::STATUSES) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
