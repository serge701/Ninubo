<div class="card card-md">
  <div class="card-body text-center">
    <i class="ti ti-database-cog text-warning" style="font-size:3rem"></i>
    <h2 class="h2 mt-2">Database update required</h2>
    <?php if (is_admin()): ?>
    <p class="text-secondary">A new version of the CRM was installed. Apply the database update to continue — it only adds tables and columns, it does not delete your data.</p>
    <div class="text-start small bg-light-subtle border rounded p-2 mb-3">
      <?php foreach ($pending as $file): ?><div><i class="ti ti-file-database me-1"></i><?= e($file) ?></div><?php endforeach; ?>
    </div>
    <form method="post" action="<?= url('/system/migrate') ?>">
      <?= csrf_field() ?>
      <button class="btn btn-warning w-100"><i class="ti ti-player-play me-1"></i>Apply update now</button>
    </form>
    <div class="form-hint mt-2">Tip: back up the database first (phpMyAdmin → Export).</div>
    <?php else: ?>
    <p class="text-secondary">The CRM is being updated. Please ask an administrator to apply the database update, then reload this page.</p>
    <?php endif; ?>
  </div>
  <div class="card-footer text-center">
    <form method="post" action="<?= url('/logout') ?>" class="m-0"><?= csrf_field() ?><button class="btn btn-link btn-sm">Sign out</button></form>
  </div>
</div>
