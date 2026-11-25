<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(($title ?? 'Sign in') . ' · Ninubo CRM') ?></title>
  <link rel="icon" href="<?= e(NINUBO_BASE . '/favicon.ico') ?>">
  <link rel="stylesheet" href="<?= asset('tabler/css/tabler.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('tabler/icons/tabler-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('app/app.css') ?>">
</head>
<body class="d-flex flex-column crm-auth">
<div class="page page-center">
  <div class="container <?= e($authWidth ?? 'container-tight') ?> py-4">
    <div class="text-center mb-4">
      <a href="<?= url('/') ?>" class="crm-brand">
        <img src="<?= e(NINUBO_BASE . '/assets/img/ninubo.png') ?>" alt="Ninubo" class="crm-auth-logo">
        <span class="crm-brand-tag">CRM</span>
      </a>
    </div>
    <?= \Crm\Core\View::partial('partials/flash') ?>
    <?= $content ?>
  </div>
</div>
<script src="<?= asset('tabler/js/tabler.min.js') ?>"></script>
<script src="<?= asset('app/app.js') ?>"></script>
</body>
</html>
