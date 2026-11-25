<?php
/**
 * "New website brief" modal. From a client page: $clientId set, $clientOptions null.
 * From the briefs list: $clientId null, $clientOptions = id => name.
 */
use Crm\Models\Brief;
use Crm\Models\Package;
?>
<div class="modal modal-blur fade" id="new-brief" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form class="modal-content" method="post" action="<?= url('/briefs') ?>">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title">New website brief</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if ($clientId): ?>
          <input type="hidden" name="client_id" value="<?= (int)$clientId ?>">
          <input type="hidden" name="from" value="client">
        <?php else: ?>
          <div class="mb-3">
            <label class="form-label required" for="brief_client">Client</label>
            <?php if (!$clientOptions): ?>
              <div class="alert alert-info mb-0">There are no clients yet. <a href="<?= url('/clients/new') ?>">Create the client first</a> — every sale starts there.</div>
            <?php else: ?>
              <select class="form-select" id="brief_client" name="client_id" required>
                <option value="">Choose a client…</option>
                <?php foreach ($clientOptions as $cid => $name): ?>
                <option value="<?= (int)$cid ?>"><?= e($name) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-hint">New client? <a href="<?= url('/clients/new') ?>">Create them first</a>.</div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <label class="form-label required">Website package</label>
        <?php if (!$briefPackages): ?>
          <div class="alert alert-warning mb-0">No active website packages with a brief. <?= is_admin() ? '<a href="' . url('/catalog') . '">Set one up in the catalog</a>.' : 'Ask an administrator to set one up in the catalog.' ?></div>
        <?php else: ?>
          <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
            <?php foreach ($briefPackages as $i => $p): ?>
            <label class="form-selectgroup-item flex-fill">
              <input type="radio" name="package_id" value="<?= (int)$p['id'] ?>" class="form-selectgroup-input"<?= $i === 0 ? ' checked' : '' ?>>
              <div class="form-selectgroup-label d-flex align-items-center p-3">
                <div class="me-3"><span class="form-selectgroup-check"></span></div>
                <div class="form-selectgroup-label-content text-start">
                  <div class="fw-medium"><?= e($p['name']) ?> <span class="text-secondary fw-normal">· <?= e(Package::priceLabel($p)) ?></span></div>
                  <div class="small text-secondary"><?= e(Brief::profileLabel($p['brief_profile'])) ?> — <?= count(ninubo_brief_schema('en', $p['brief_profile'])) ?> steps</div>
                </div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="form-hint mt-2">The client only sees the questions for this package.<?php if (!empty($clientLanguage)): ?> The form opens in <?= $clientLanguage === 'es' ? 'Spanish' : 'English' ?> (client's preferred language); they can switch.<?php endif; ?></div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"<?= (!$briefPackages || ($clientId === null && !$clientOptions)) ? ' disabled' : '' ?>>Create link</button>
      </div>
    </form>
  </div>
</div>
