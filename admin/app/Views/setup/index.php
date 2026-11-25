<div class="card card-md">
  <div class="card-body">
    <h2 class="h2 text-center mb-2">Set up Ninubo CRM</h2>
    <p class="text-secondary text-center mb-4">
      This screen appears only once. It creates the CRM tables and your administrator account.
    </p>
    <?php if ($pending): ?>
    <div class="alert alert-info small">
      <?= count($pending) ?> database change(s) will be applied: <?= e(implode(', ', $pending)) ?>
    </div>
    <?php endif; ?>
    <form method="post" action="<?= url('/setup') ?>" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label required" for="setup_key">Current admin-panel password</label>
        <input type="password" class="form-control<?= invalid('setup_key') ?>" id="setup_key" name="setup_key" required autocomplete="off">
        <?php if ($err = field_error('setup_key')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
        <div class="form-hint">The single password the old briefs panel used. It proves you own this installation.</div>
      </div>
      <hr class="my-3">
      <h3 class="h4">Your administrator account</h3>
      <div class="mb-3">
        <label class="form-label required" for="name">Full name</label>
        <input type="text" class="form-control<?= invalid('name') ?>" id="name" name="name" value="<?= e(old('name')) ?>" required>
        <?php if ($err = field_error('name')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label required" for="username">Username</label>
        <input type="text" class="form-control<?= invalid('username') ?>" id="username" name="username" value="<?= e(old('username')) ?>" autocapitalize="none" required>
        <?php if ($err = field_error('username')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control<?= invalid('email') ?>" id="email" name="email" value="<?= e(old('email')) ?>">
        <?php if ($err = field_error('email')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label required" for="password">Password</label>
        <input type="password" class="form-control<?= invalid('password') ?>" id="password" name="password" autocomplete="new-password" required>
        <?php if ($err = field_error('password')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
        <div class="form-hint">At least 10 characters, with letters and a number.</div>
      </div>
      <div class="mb-3">
        <label class="form-label required" for="password_confirm">Confirm password</label>
        <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" required>
      </div>
      <div class="form-footer">
        <button type="submit" class="btn btn-primary w-100">Create tables and administrator</button>
      </div>
    </form>
  </div>
</div>
