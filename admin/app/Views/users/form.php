<?php
use Crm\Core\Session;
use Crm\Core\View;
use Crm\Models\User;

$isNew = $editUser === null;
$u = $editUser ?? [];
$val = fn(string $k, $d = '') => old($k, $u[$k] ?? $d);
$temp = Session::peekFlash('temp_password');
$isSelf = !$isNew && (int)$u['id'] === (int)auth_user()['id'];
?>
<?php View::start('actions'); ?>
<a href="<?= url('/users') ?>" class="btn"><i class="ti ti-arrow-left me-1"></i>All users</a>
<?php View::end(); ?>

<?php if ($temp): ?>
<div class="alert alert-info">
  <div class="fw-bold mb-1">Temporary password for <?= e($temp['username']) ?></div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <code class="fs-3"><?= e($temp['password']) ?></code>
    <button type="button" class="btn btn-sm" data-copy="<?= e($temp['password']) ?>"><i class="ti ti-copy me-1"></i>Copy</button>
  </div>
  <div class="small text-secondary mt-1">It is shown only this once. The user must choose a new password when signing in.</div>
</div>
<?php endif; ?>

<div class="row row-cards">
  <div class="col-lg-8">
    <form class="card" method="post" action="<?= url($isNew ? '/users' : '/users/' . (int)$u['id']) ?>" novalidate>
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label required" for="name">Full name</label>
            <input type="text" class="form-control<?= invalid('name') ?>" id="name" name="name" value="<?= e($val('name')) ?>" required>
            <?php if ($err = field_error('name')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
          </div>
          <div class="col-md-6">
            <label class="form-label<?= $isNew ? ' required' : '' ?>" for="username">Username</label>
            <?php if ($isNew): ?>
              <input type="text" class="form-control<?= invalid('username') ?>" id="username" name="username" value="<?= e($val('username')) ?>" autocapitalize="none" required>
              <?php if ($err = field_error('username')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
              <div class="form-hint">Used to sign in. Lowercase letters, numbers, dot, dash or underscore.</div>
            <?php else: ?>
              <input type="text" class="form-control" id="username" value="<?= e($u['username']) ?>" disabled>
              <div class="form-hint">Usernames cannot be changed.</div>
            <?php endif; ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control<?= invalid('email') ?>" id="email" name="email" value="<?= e($val('email')) ?>">
            <?php if ($err = field_error('email')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="phone">Phone</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?= e($val('phone')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label required" for="role">Role</label>
            <select class="form-select<?= invalid('role') ?>" id="role" name="role">
              <?php foreach (User::ROLES as $key => $label): ?>
              <option value="<?= e($key) ?>"<?= $val('role', 'sales') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($err = field_error('role')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
            <div class="form-hint">Sales: clients, invoices, contracts and briefs. Administrator: also users, catalog and settings.</div>
          </div>
          <?php if (!$isNew): ?>
          <div class="col-md-6">
            <label class="form-label">Access</label>
            <label class="form-check form-switch mt-2">
              <input class="form-check-input" type="checkbox" name="is_active" value="1"<?= (int)old('is_active', $u['is_active']) ? ' checked' : '' ?><?= $isSelf ? ' disabled' : '' ?>>
              <span class="form-check-label">Account active</span>
            </label>
            <?php if ($isSelf): ?><input type="hidden" name="is_active" value="1"><div class="form-hint">You cannot disable your own account.</div><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary"><?= $isNew ? 'Create user' : 'Save changes' ?></button>
      </div>
    </form>
  </div>

  <div class="col-lg-4">
    <?php if ($isNew): ?>
    <div class="card"><div class="card-body text-secondary">
      A temporary password is generated when you create the user. Share it with them privately; they will be asked to choose their own at first sign-in.
    </div></div>
    <?php else: ?>
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">Password</h3>
        <p class="text-secondary">Generates a new temporary password. The user must change it at their next sign-in.</p>
        <form method="post" action="<?= url('/users/' . (int)$u['id'] . '/reset-password') ?>" data-confirm="Reset the password for <?= e($u['name']) ?>? Their current password will stop working.">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-warning w-100"><i class="ti ti-key me-1"></i>Reset password</button>
        </form>
      </div>
      <div class="card-footer small text-secondary">
        Created <?= e(fmt_date($u['created_at'])) ?> · Last sign-in <?= e(fmt_datetime($u['last_login_at'])) ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
