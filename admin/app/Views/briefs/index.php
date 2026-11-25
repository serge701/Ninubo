<?php
use Crm\Core\View;
use Crm\Models\Brief;
?>
<?php View::start('actions'); ?>
<a href="#new-brief" class="btn btn-primary" data-bs-toggle="modal"><i class="ti ti-plus me-1"></i>New brief link</a>
<?php View::end(); ?>

<p class="text-secondary">
  Each brief is a private link where a client fills in the information for their new website (in English or Spanish).
  Progress is saved automatically and you can read the answers here.
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
      <p class="empty-subtitle text-secondary">Create a link and send it to your client.</p>
    </div>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr><th>Client</th><th>Status</th><th>Language</th><th>Created</th><th>Last update</th><th class="w-1"></th></tr>
      </thead>
      <tbody>
      <?php foreach ($briefs as $b): ?>
        <tr>
          <td>
            <a href="<?= url('/briefs/' . (int)$b['id']) ?>" class="fw-medium"><?= e($b['client_label']) ?></a>
            <?php if ($b['created_by_name']): ?><div class="small text-secondary">by <?= e($b['created_by_name']) ?></div><?php endif; ?>
          </td>
          <td><?= status_badge($b['status'], Brief::STATUS_COLORS, Brief::STATUSES) ?></td>
          <td><?= $b['lang'] === 'en' ? 'English' : 'Spanish' ?></td>
          <td class="text-secondary"><?= e(fmt_date($b['created_at'])) ?></td>
          <td class="text-secondary"><?= e(fmt_datetime($b['updated_at'])) ?></td>
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

<div class="modal modal-blur fade" id="new-brief" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content" method="post" action="<?= url('/briefs') ?>">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title">New brief link</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label required" for="client_label">Client name</label>
        <input type="text" class="form-control<?= invalid('client_label') ?>" id="client_label" name="client_label" value="<?= e(old('client_label')) ?>" placeholder="e.g. ABC Roofing — McAllen" required maxlength="190">
        <?php if ($err = field_error('client_label')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
        <div class="form-hint">Only for you to identify the link. The client sees it at the top of their form.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Create link</button>
      </div>
    </form>
  </div>
</div>
<?php if (field_error('client_label')): ?>
<script>document.addEventListener('DOMContentLoaded', function () { window.tabler && new tabler.Modal(document.getElementById('new-brief')).show(); });</script>
<?php endif; ?>
