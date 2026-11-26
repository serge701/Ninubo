<?php
use Crm\Models\Client;
use Crm\Models\Contract;
?>
<div class="card">
  <div class="card-header">
    <ul class="nav nav-pills card-header-pills flex-wrap">
      <li class="nav-item"><a class="nav-link<?= $status === '' ? ' active' : '' ?>" href="<?= url('/contracts') ?>">All <span class="badge ms-1"><?= array_sum($counts) ?></span></a></li>
      <?php foreach (Contract::STATUSES as $key => $label): ?>
      <li class="nav-item"><a class="nav-link<?= $status === $key ? ' active' : '' ?>" href="<?= url('/contracts?status=' . $key) ?>"><?= e($label) ?> <span class="badge ms-1"><?= (int)$counts[$key] ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php if (!$contracts): ?>
  <div class="card-body">
    <div class="empty">
      <div class="empty-icon"><i class="ti ti-file-certificate" style="font-size:2.5rem"></i></div>
      <p class="empty-title">No contracts here</p>
      <p class="empty-subtitle text-secondary">Contracts are created from a client, after issuing their invoice: open the client and choose New → Contract.</p>
      <div class="empty-action"><a href="<?= url('/clients') ?>" class="btn btn-primary"><i class="ti ti-address-book me-1"></i>Go to clients</a></div>
    </div>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead><tr><th>Contract</th><th>Client</th><th>Invoice</th><th>Effective</th><th>Status</th><th>Last update</th></tr></thead>
      <tbody>
      <?php foreach ($contracts as $k): ?>
        <tr>
          <td><a href="<?= url('/contracts/' . (int)$k['id']) ?>" class="fw-medium"><?= e(Contract::displayNumber($k)) ?></a></td>
          <td><a href="<?= url('/clients/' . (int)$k['client_id']) ?>" class="text-reset"><?= e(Client::displayName($k)) ?></a></td>
          <td class="text-secondary"><?= e($k['invoice_number'] ?: '—') ?></td>
          <td class="text-secondary"><?= e(fmt_date($k['effective_date'])) ?></td>
          <td><?= status_badge($k['status'], Contract::STATUS_COLORS, Contract::STATUSES) ?></td>
          <td class="text-secondary"><?= e(time_ago($k['updated_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
