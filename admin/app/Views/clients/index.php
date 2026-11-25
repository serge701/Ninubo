<?php
use Crm\Core\View;
use Crm\Models\Client;

$assignedOptions = ['' => 'Anyone', 'none' => 'Unassigned'];
foreach ($users as $u) {
    $assignedOptions[(string)$u['id']] = $u['name'];
}
?>
<?php View::start('actions'); ?>
<a href="<?= url('/clients/new') ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New client</a>
<?php View::end(); ?>

<div class="card">
  <div class="card-header">
    <ul class="nav nav-pills card-header-pills">
      <li class="nav-item"><a class="nav-link<?= $filters['status'] === '' ? ' active' : '' ?>" href="<?= url('/clients') . query_with(['status' => null, 'page' => null]) ?>">All <span class="badge ms-1"><?= array_sum($counts) ?></span></a></li>
      <?php foreach (Client::STATUSES as $key => $label): ?>
      <li class="nav-item"><a class="nav-link<?= $filters['status'] === $key ? ' active' : '' ?>" href="<?= url('/clients') . query_with(['status' => $key, 'page' => null]) ?>"><?= e($label) ?>s <span class="badge ms-1"><?= (int)$counts[$key] ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="card-body border-bottom py-3">
    <form method="get" action="<?= url('/clients') ?>" class="row g-2 align-items-end">
      <input type="hidden" name="status" value="<?= e($filters['status']) ?>">
      <div class="col-md-6">
        <div class="input-icon">
          <span class="input-icon-addon"><i class="ti ti-search"></i></span>
          <input type="search" class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Search name, company, email, phone, city or ZIP…">
        </div>
      </div>
      <div class="col-md-4">
        <select class="form-select" name="assigned" aria-label="Assigned to">
          <?php foreach ($assignedOptions as $value => $label): ?>
          <option value="<?= e($value) ?>"<?= $filters['assigned'] === (string)$value ? ' selected' : '' ?>><?= $value === '' ? 'Assigned to: anyone' : e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid"><button class="btn">Filter</button></div>
    </form>
  </div>

  <?php if (!$clients): ?>
  <div class="card-body">
    <div class="empty">
      <div class="empty-icon"><i class="ti ti-address-book" style="font-size:2.5rem"></i></div>
      <p class="empty-title"><?= $filters['q'] !== '' || $filters['status'] !== '' || $filters['assigned'] !== '' ? 'No clients match' : 'No clients yet' ?></p>
      <p class="empty-subtitle text-secondary">Every sale starts with a client. Create one to send briefs, invoices and contracts.</p>
      <div class="empty-action"><a href="<?= url('/clients/new') ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New client</a></div>
    </div>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr><th>Client</th><th>Contact</th><th>Status</th><th>Assigned to</th><th>Last activity</th></tr>
      </thead>
      <tbody>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td>
            <a href="<?= url('/clients/' . (int)$c['id']) ?>" class="fw-medium"><?= e(Client::displayName($c)) ?></a>
            <div class="small text-secondary"><?= e(implode(' · ', array_filter([$c['industry'], $c['city']]))) ?: '&nbsp;' ?></div>
          </td>
          <td>
            <?php if ($c['company_name'] !== ''): ?><div><?= e($c['contact_name']) ?></div><?php endif; ?>
            <div class="small text-secondary"><?= e(implode(' · ', array_filter([$c['phone'], $c['email']]))) ?></div>
          </td>
          <td><?= status_badge($c['status'], Client::STATUS_COLORS, Client::STATUSES) ?></td>
          <td><?= $c['assigned_name'] ? e($c['assigned_name']) : '<span class="text-secondary">—</span>' ?></td>
          <td class="text-secondary"><?= e(time_ago($c['last_activity_at'] ?? $c['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer d-flex align-items-center">
    <p class="m-0 text-secondary"><?= (int)$total ?> client<?= $total === 1 ? '' : 's' ?></p>
    <?php if ($pages > 1): ?>
    <ul class="pagination m-0 ms-auto">
      <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="<?= url('/clients') . query_with(['page' => $page - 1]) ?>"><i class="ti ti-chevron-left"></i></a></li>
      <?php for ($i = 1; $i <= $pages; $i++): ?>
      <li class="page-item<?= $i === $page ? ' active' : '' ?>"><a class="page-link" href="<?= url('/clients') . query_with(['page' => $i]) ?>"><?= $i ?></a></li>
      <?php endfor; ?>
      <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>"><a class="page-link" href="<?= url('/clients') . query_with(['page' => $page + 1]) ?>"><i class="ti ti-chevron-right"></i></a></li>
    </ul>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
