<header class="header">
  <nav class="nav container">
    <a href="<?= ninubo_url() ?>" class="logo">
      <img src="<?= ninubo_url('assets/img/ninubo.png') ?>" alt="NINUBO" class="logo-img" width="140" height="36">
    </a>
    <button class="nav-toggle" aria-label="Abrir menú" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <ul class="nav-links">
      <li><a href="<?= ninubo_url() ?>" class="<?= $currentPage === 'home' ? 'is-active' : '' ?>">Inicio</a></li>
      <li class="nav-dropdown">
        <a href="<?= ninubo_url('servicios.php') ?>" class="nav-dropdown-toggle <?= in_array($currentPage, ['servicios','sistemas','web','data']) ? 'is-active' : '' ?>">
          Servicios
          <svg class="nav-dropdown-arrow" width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg>
        </a>
        <ul class="nav-dropdown-menu">
          <li><a href="<?= ninubo_url('sistemas.php') ?>">Sistemas Web</a></li>
          <li><a href="<?= ninubo_url('paginas-web.php') ?>">Páginas Web</a></li>
          <li><a href="<?= ninubo_url('data-analysis.php') ?>">Data Analysis</a></li>
        </ul>
      </li>
      <li class="nav-dropdown">
        <a href="<?= ninubo_url('capacitaciones.php') ?>" class="nav-dropdown-toggle <?= in_array($currentPage, ['capacitaciones','curso-sql']) ? 'is-active' : '' ?>">
          Capacitaciones
          <svg class="nav-dropdown-arrow" width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg>
        </a>
        <ul class="nav-dropdown-menu">
          <li><a href="<?= ninubo_url('curso-sql.php') ?>">SQL para Consulta y Análisis de Datos</a></li>
        </ul>
      </li>
      <li><a href="<?= ninubo_url('portafolio.php') ?>" class="<?= $currentPage === 'portafolio' ? 'is-active' : '' ?>">Portafolio</a></li>
      <li><a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary btn-sm">Contacto</a></li>
      <li class="language-switcher" aria-label="Idioma / Language">
        <a href="<?= ninubo_language_url('es') ?>" class="language-option <?= NINUBO_LANG === 'es' ? 'is-active' : '' ?>" lang="es" aria-label="Español">
          <span aria-hidden="true">🇲🇽</span><span>ES</span>
        </a>
        <a href="<?= ninubo_language_url('en') ?>" class="language-option <?= NINUBO_LANG === 'en' ? 'is-active' : '' ?>" lang="en" aria-label="English">
          <span aria-hidden="true">🇺🇸</span><span>EN</span>
        </a>
      </li>
    </ul>
  </nav>
</header>
