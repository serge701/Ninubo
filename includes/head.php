<?php
require_once __DIR__ . '/config.php';
$pageTitle = $pageTitle ?? 'NINUBO';
$pageDesc = $pageDesc ?? 'Desarrollo de sistemas web, análisis de datos y sitios web a la medida para empresas.';
$currentPage = $currentPage ?? '';
$canonicalUrl = ninubo_absolute_url(basename($_SERVER['PHP_SELF'] ?? 'index.php') === 'index.php' ? '' : basename($_SERVER['PHP_SELF']), NINUBO_LANG);
$spanishUrl = ninubo_absolute_url(basename($_SERVER['PHP_SELF'] ?? 'index.php') === 'index.php' ? '' : basename($_SERVER['PHP_SELF']), 'es');
$englishUrl = ninubo_absolute_url(basename($_SERVER['PHP_SELF'] ?? 'index.php') === 'index.php' ? '' : basename($_SERVER['PHP_SELF']), 'en');
$socialImage = ninubo_absolute_url('assets/img/ninubo.png', 'es');
$locale = NINUBO_LANG === 'en' ? 'en_US' : 'es_MX';
?>
<!DOCTYPE html>
<html lang="<?= NINUBO_LANG ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
<?php if (!empty($pageNoindex)): ?>
  <meta name="robots" content="noindex, nofollow">
<?php endif; ?>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
  <link rel="alternate" hreflang="es-MX" href="<?= htmlspecialchars($spanishUrl) ?>">
  <link rel="alternate" hreflang="en-US" href="<?= htmlspecialchars($englishUrl) ?>">
  <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($spanishUrl) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="<?= $locale ?>">
  <meta property="og:site_name" content="NINUBO">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($socialImage) ?>">
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($socialImage) ?>">
  <link rel="icon" href="<?= ninubo_url('favicon.ico') ?>" sizes="any">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ninubo_url('assets/css/main.css') ?>">
  <script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'NINUBO',
    'url' => ninubo_absolute_url('', 'es'),
    'inLanguage' => NINUBO_LANG === 'en' ? 'en-US' : 'es-MX',
    'description' => $pageDesc,
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php if (NINUBO_GA_MEASUREMENT_ID !== ''): ?>
  <script>
    // Google Analytics is only actually loaded after the visitor accepts the
    // cookie banner (see ninuboLoadAnalytics() in main.js) — this just makes
    // the measurement ID available to that code.
    window.NINUBO_GA_ID = <?= json_encode(NINUBO_GA_MEASUREMENT_ID) ?>;
  </script>
<?php endif; ?>
</head>
<body>
