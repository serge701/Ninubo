<div class="row">
  <div class="col-lg-6">
    <?php if ($forced): ?>
    <div class="alert alert-warning">You are using a temporary password. Choose your own to continue.</div>
    <?php endif; ?>
    <form class="card" method="post" action="<?= url('/profile/password') ?>" novalidate>
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label required" for="current_password"><?= $forced ? 'Temporary password' : 'Current password' ?></label>
          <input type="password" class="form-control<?= invalid('current_password') ?>" id="current_password" name="current_password" autocomplete="current-password" required>
          <?php if ($err = field_error('current_password')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label required" for="password">New password</label>
          <input type="password" class="form-control<?= invalid('password') ?>" id="password" name="password" autocomplete="new-password" required>
          <?php if ($err = field_error('password')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
          <div class="form-hint">At least 10 characters, with letters and a number.</div>
        </div>
        <div class="mb-1">
          <label class="form-label required" for="password_confirm">Confirm new password</label>
          <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
        </div>
      </div>
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">Change password</button>
      </div>
    </form>
  </div>
</div>
