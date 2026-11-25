<div class="card card-md">
  <div class="card-body">
    <h2 class="h2 text-center mb-4">Sign in to your account</h2>
    <form method="post" action="<?= url('/login') ?>" autocomplete="on" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?= e(old('username')) ?>" autocomplete="username" autocapitalize="none" required autofocus>
      </div>
      <div class="mb-2">
        <label class="form-label" for="password">Password</label>
        <div class="input-group input-group-flat">
          <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
          <span class="input-group-text">
            <a href="#" class="link-secondary" data-toggle-password="#password" title="Show password"><i class="ti ti-eye"></i></a>
          </span>
        </div>
      </div>
      <div class="form-footer">
        <button type="submit" class="btn btn-primary w-100">Sign in</button>
      </div>
    </form>
  </div>
</div>
<div class="text-center text-secondary mt-3 small">Forgot your password? Ask an administrator to reset it.</div>
