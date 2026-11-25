<?php
use Crm\Models\User;

$val = fn(string $k) => old($k, $profile[$k] ?? '');
?>
<div class="row row-cards">
  <div class="col-lg-8">
    <form class="card" method="post" action="<?= url('/profile') ?>" novalidate>
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label required" for="name">Full name</label>
            <input type="text" class="form-control<?= invalid('name') ?>" id="name" name="name" value="<?= e($val('name')) ?>" required>
            <?php if ($err = field_error('name')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="username">Username</label>
            <input type="text" class="form-control" id="username" value="<?= e($profile['username']) ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control<?= invalid('email') ?>" id="email" name="email" value="<?= e($val('email')) ?>">
            <?php if ($err = field_error('email')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="phone">Phone</label>
            <input type="tel" class="form-control<?= invalid('phone') ?>" id="phone" name="phone" value="<?= e($val('phone')) ?>" inputmode="tel" placeholder="(956) 555-0123">
            <?php if ($err = field_error('phone')): ?><div class="invalid-feedback"><?= e($err) ?></div><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <span class="text-secondary small">Role: <?= e(User::ROLES[$profile['role']] ?? $profile['role']) ?></span>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
  <div class="col-lg-4">
    <div class="card"><div class="card-body">
      <h3 class="card-title">Password</h3>
      <a href="<?= url('/profile/password') ?>" class="btn w-100"><i class="ti ti-key me-1"></i>Change password</a>
    </div></div>
  </div>
</div>
