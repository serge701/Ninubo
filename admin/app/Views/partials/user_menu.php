<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
  <span class="dropdown-header"><?= e('@' . $me['username']) ?></span>
  <a href="<?= url('/profile') ?>" class="dropdown-item"><i class="ti ti-user dropdown-item-icon"></i>My profile</a>
  <a href="<?= url('/profile/password') ?>" class="dropdown-item"><i class="ti ti-key dropdown-item-icon"></i>Change password</a>
  <div class="dropdown-divider"></div>
  <form method="post" action="<?= url('/logout') ?>" class="m-0">
    <?= csrf_field() ?>
    <button type="submit" class="dropdown-item"><i class="ti ti-logout dropdown-item-icon"></i>Sign out</button>
  </form>
</div>
