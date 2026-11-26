<?php
/**
 * Main CRM layout — Tabler 1.4, vertical (sidebar) navigation.
 * Controllers pass $title (and optionally $pretitle); views put header buttons between
 * View::start('actions') ... View::end().
 */
use Crm\Core\App;
use Crm\Core\Migrator;
use Crm\Models\User;

$me = auth_user();
$path = App::currentPath();
$active = fn(string $prefix): string => ($prefix === '/' ? $path === '/' : str_starts_with($path, $prefix)) ? ' active' : '';
$pendingMigrations = ($me && $me['role'] === 'admin') ? Migrator::pending() : [];

/** Sidebar entries: [path, icon, label, adminOnly]. Modules appear here as they are built. */
$nav = [
    ['/', 'layout-dashboard', 'Dashboard', false],
    ['/clients', 'address-book', 'Clients', false],
    ['/invoices', 'file-invoice', 'Invoices', false],
    ['/contracts', 'file-certificate', 'Contracts', false],
    ['/briefs', 'forms', 'Website briefs', false],
    ['/catalog', 'package', 'Catalog', false],
];
$adminNav = [
    ['/users', 'users', 'Users'],
    ['/settings', 'settings', 'Business settings'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(($title ?? 'CRM') . ' · Ninubo CRM') ?></title>
  <link rel="icon" href="<?= e(NINUBO_BASE . '/favicon.ico') ?>">
  <script>
    (function () {
      var t = 'light';
      try { t = localStorage.getItem('crm-theme') || 'light'; } catch (e) {}
      document.documentElement.setAttribute('data-bs-theme', t);
    })();
  </script>
  <link rel="stylesheet" href="<?= asset('tabler/css/tabler.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('tabler/icons/tabler-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('app/app.css') ?>">
</head>
<body>
<div class="page">
  <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-brand navbar-brand-autodark">
        <a href="<?= url('/') ?>" class="crm-brand">
          <img src="<?= e(NINUBO_BASE . '/assets/img/ninubo.png') ?>" alt="Ninubo" class="crm-brand-logo">
          <span class="crm-brand-tag">CRM</span>
        </a>
      </div>
      <?php if ($me): ?>
      <div class="navbar-nav flex-row d-lg-none">
        <div class="nav-item dropdown">
          <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
            <span class="avatar avatar-sm"><?= e(initials($me['name'])) ?></span>
          </a>
          <?= \Crm\Core\View::partial('partials/user_menu', ['me' => $me]) ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="collapse navbar-collapse" id="sidebar-menu">
        <ul class="navbar-nav pt-lg-3">
          <?php foreach ($nav as [$href, $icon, $label]): ?>
          <li class="nav-item<?= $active($href) ?>">
            <a class="nav-link" href="<?= url($href) ?>">
              <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-<?= e($icon) ?>"></i></span>
              <span class="nav-link-title"><?= e($label) ?></span>
            </a>
          </li>
          <?php endforeach; ?>

          <?php if ($me && $me['role'] === 'admin'): ?>
          <li class="nav-item mt-3"><div class="crm-nav-heading">Administration</div></li>
          <?php foreach ($adminNav as [$href, $icon, $label]): ?>
          <li class="nav-item<?= $active($href) ?>">
            <a class="nav-link" href="<?= url($href) ?>">
              <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-<?= e($icon) ?>"></i></span>
              <span class="nav-link-title"><?= e($label) ?></span>
            </a>
          </li>
          <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </aside>

  <header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
    <div class="container-xl">
      <div class="navbar-nav flex-row order-md-last ms-auto">
        <div class="nav-item">
          <a href="<?= e(NINUBO_BASE . '/') ?>" target="_blank" rel="noopener" class="nav-link px-2" title="Open ninubo.com">
            <i class="ti ti-world" style="font-size:1.25rem"></i>
          </a>
        </div>
        <div class="nav-item">
          <a href="#" class="nav-link px-2" data-theme-toggle title="Light / dark mode">
            <i class="ti ti-moon crm-theme-icon-dark" style="font-size:1.25rem"></i>
            <i class="ti ti-sun crm-theme-icon-light" style="font-size:1.25rem"></i>
          </a>
        </div>
        <?php if ($me): ?>
        <div class="nav-item dropdown">
          <a href="#" class="nav-link d-flex lh-1 text-reset p-0 ps-2" data-bs-toggle="dropdown" aria-label="Open user menu">
            <span class="avatar avatar-sm"><?= e(initials($me['name'])) ?></span>
            <div class="d-none d-xl-block ps-2">
              <div><?= e($me['name']) ?></div>
              <div class="mt-1 small text-secondary"><?= e(User::ROLES[$me['role']] ?? $me['role']) ?></div>
            </div>
          </a>
          <?= \Crm\Core\View::partial('partials/user_menu', ['me' => $me]) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <div class="page-wrapper">
    <div class="page-header d-print-none">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <?php if (!empty($pretitle)): ?><div class="page-pretitle"><?= e($pretitle) ?></div><?php endif; ?>
            <h2 class="page-title"><?= e($title ?? '') ?></h2>
          </div>
          <?php if (($actions = \Crm\Core\View::section('actions')) !== ''): ?>
          <div class="col-auto ms-auto d-print-none"><div class="btn-list"><?= $actions ?></div></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="page-body">
      <div class="container-xl">
        <?php if ($pendingMigrations): ?>
        <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
          <div><i class="ti ti-database-cog me-1"></i> <strong>Database update pending</strong> — <?= count($pendingMigrations) ?> change(s) need to be applied: <?= e(implode(', ', $pendingMigrations)) ?></div>
          <form method="post" action="<?= url('/system/migrate') ?>" class="m-0"><?= csrf_field() ?><button class="btn btn-warning btn-sm">Apply now</button></form>
        </div>
        <?php endif; ?>
        <?= \Crm\Core\View::partial('partials/flash') ?>
        <?= $content ?>
      </div>
    </div>

    <footer class="footer footer-transparent d-print-none">
      <div class="container-xl">
        <div class="text-secondary small">&copy; <?= date('Y') ?> Ninubo CRM</div>
      </div>
    </footer>
  </div>
</div>
<script src="<?= asset('tabler/js/tabler.min.js') ?>"></script>
<script src="<?= asset('app/app.js') ?>"></script>
</body>
</html>
