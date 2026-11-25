<?php
use Crm\Core\View;
use Crm\Models\Brief;
?>
<?php View::start('actions'); ?>
<a href="#new-brief" class="btn btn-primary" data-bs-toggle="modal"><i class="ti ti-plus me-1"></i>New brief link</a>
<?php View::end(); ?>

<p class="text-secondary">
  A brief is a private link where the client fills in the information for their new website, with the questions of the
  package they bought. Briefs are normally created from the client's page.
</p>

<div class="card">
  <div class="card-header">
    <ul class="nav nav-pills card-header-pills">
      <li class="nav-item"><a class="nav-link<?= $status === '' ? ' active' : '' ?>" href="<?= url('/briefs') ?>">All <span class="badge ms-1"><?= array_sum($counts) ?></span></a></li>
      <?php foreach (Brief::STATUSES as $key => $label): ?>
      <li class="nav-item"><a class="nav-link<?= $status === $key ? ' active' : '' ?>" href="<?= url('/briefs?status=' . $key) ?>"><?= e($label) ?> <span class="badge ms-1"><?= (int)$counts[$key] ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php if (!$briefs): ?>
  <div class="card-body">
    <div class="empty">
      <div class="empty-icon"><i class="ti ti-forms" style="font-size:2.5rem"></i></div>
      <p class="empty-title">No briefs here</p>
      <p class="empty-subtitle text-secondary">Open a client and choose New → Website brief.</p>
    </div>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr><th>Client</th><th>Package</th><th>Status</th><th>Language</th><th>Last update</th><th class="w-1"></th></tr>
      </thead>
      <tbody>
      <?php foreach ($briefs as $b): ?>
        <tr>
          <td>
            <a href="<?= url('/briefs/' . (int)$b['id']) ?>" class="fw-medium"><?= e($b['client_label']) ?></a>
            <div class="small text-secondary">
              <?php if ($b['client_id']): ?><a href="<?= url('/clients/' . (int)$b['client_id']) ?>" class="text-secondary"><i class="ti ti-address-book"></i> Client page</a><?php else: ?>Not linked to a client<?php endif; ?>
            </div>
          </td>
          <td><?= e($b['package_name'] ?: '—') ?><div class="small text-secondary"><?= e(Brief::profileLabel($b['brief_profile'])) ?></div></td>
          <td><?= status_badge($b['status'], Brief::STATUS_COLORS, Brief::STATUSES) ?></td>
          <td><?= $b['lang'] === 'en' ? 'English' : 'Spanish' ?></td>
          <td class="text-secondary"><?= e(time_ago($b['updated_at'])) ?></td>
          <td>
            <div class="btn-list flex-nowrap">
              <a href="<?= url('/briefs/' . (int)$b['id']) ?>" class="btn btn-sm">Open</a>
              <button type="button" class="btn btn-sm" data-copy="<?= e(Brief::publicLink($b)) ?>"><i class="ti ti-copy me-1"></i>Copy link</button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?= View::partial('briefs/_new_modal', [
    'clientId' => null,
    'clientLanguage' => null,
    'briefPackages' => $briefPackages,
    'clientOptions' => $clientOptions,
]) ?>
