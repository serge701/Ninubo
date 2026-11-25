<?php
$pageTitle = 'NINUBO | Sistemas web, datos y páginas web a la medida';
$pageDesc = 'NINUBO reúne tres servicios para empresas: sistemas web a la medida, análisis de datos y páginas web profesionales en México y Texas.';
$currentPage = 'home';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero page-hero--large">
    <canvas class="hero-network" aria-hidden="true"></canvas>
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <p class="hero-badge"><span class="badge-dot"></span> Sistemas web · Datos · Páginas web</p>
        <h1>Tres servicios digitales. <span class="text-gradient">Una solución clara para tu empresa.</span></h1>
        <p class="page-hero-lead">
          NINUBO se especializa en tres áreas: <strong>sistemas web a la medida</strong> para operar mejor, <strong>análisis de datos</strong> para decidir con información confiable y <strong>páginas web profesionales</strong> para atraer oportunidades.
        </p>
        <div class="page-hero-actions">
          <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Solicitar cotización</a>
          <a href="<?= ninubo_url('servicios.php') ?>" class="btn btn-ghost">Ver servicios</a>
        </div>
        <div class="hero-pillars">
          <a href="<?= ninubo_url('sistemas.php') ?>" class="hero-pillar-link">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
            Sistemas web a la medida
          </a>
          <a href="<?= ninubo_url('data-analysis.php') ?>" class="hero-pillar-link">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
            Análisis de datos y BI
          </a>
          <a href="<?= ninubo_url('paginas-web.php') ?>" class="hero-pillar-link">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
            Páginas web profesionales
          </a>
        </div>
      </div>
      <div class="page-hero-media">
        <?php ninubo_img('hero-home.jpg', 'Equipo NINUBO desarrollando soluciones tecnológicas', 'media-frame--wide'); ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Nuestras áreas</p>
        <h2>Tres servicios para resolver necesidades&nbsp;reales</h2>
        <p class="section-desc">Elige el área que necesitas hoy o combínalas para construir una solución completa.</p>
      </header>

      <div class="service-blocks">
        <article class="service-block">
          <div class="service-block-media">
            <?php ninubo_img('home-sistemas.jpg', 'Sistemas web empresariales a la medida'); ?>
          </div>
          <div class="service-block-content">
            <p class="section-tag">Sistemas Web</p>
            <h2>Sistemas web que ordenan y automatizan tu operación</h2>
            <p>Convertimos procesos manuales en herramientas web seguras y accesibles: control de asistencia con ZKTECO, CRM, ERP y plataformas especializadas como MEDAPP.</p>
            <ul class="feature-list">
              <li>Control de asistencia biométrico con ZKTECO</li>
              <li>CRM con cotizaciones, clientes y campañas</li>
              <li>ERP: inventarios, producción, compras y más</li>
              <li>MEDAPP — expediente clínico y agenda médica</li>
            </ul>
            <a href="<?= ninubo_url('sistemas.php') ?>" class="btn btn-primary">Explorar sistemas</a>
          </div>
        </article>

        <article class="service-block service-block--reverse">
          <div class="service-block-media">
            <?php ninubo_img('home-web.jpg', 'Diseño y desarrollo de páginas web'); ?>
          </div>
          <div class="service-block-content">
            <p class="section-tag">Páginas Web</p>
            <h2>Páginas web que presentan tu negocio y generan contactos</h2>
            <p>Creamos sitios claros, rápidos y optimizados para móvil: desde landing pages hasta portales corporativos, siempre alineados con tu marca y objetivos comerciales.</p>
            <ul class="feature-list">
              <li>Diseño UI/UX personalizado para tu marca</li>
              <li>Sitios corporativos, catálogos y landing pages</li>
              <li>Hosting, dominio y correo profesional</li>
              <li>Identidad visual y contenido optimizado</li>
            </ul>
            <a href="<?= ninubo_url('paginas-web.php') ?>" class="btn btn-primary">Ver páginas web</a>
          </div>
        </article>

        <article class="service-block">
          <div class="service-block-media">
            <?php ninubo_img('home-data.jpg', 'Análisis de datos y business intelligence'); ?>
          </div>
          <div class="service-block-content">
            <p class="section-tag">Data Analysis</p>
            <h2>Análisis de datos que convierte información en decisiones</h2>
            <p>Unificamos, depuramos y visualizamos tus datos con procesos ETL, dashboards ejecutivos e integración con SAP y otros ERPs para que tu equipo actúe con confianza.</p>
            <ul class="feature-list">
              <li>Procesos ETL: extracción, transformación y carga</li>
              <li>Power BI, Tableau, Excel avanzado y SQL</li>
              <li>Integración con SAP, ERPs y bases de datos</li>
              <li>KPIs, reportes automáticos y alertas</li>
            </ul>
            <a href="<?= ninubo_url('data-analysis.php') ?>" class="btn btn-primary">Conocer Data Analysis</a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Nosotros</p>
        <h2>Un equipo multidisciplinario que entiende tu&nbsp;operación</h2>
        <p class="section-desc">NINUBO está formado por profesionales de distintas disciplinas — desarrollo de software, análisis de datos e ingeniería industrial — con experiencia real dentro de la industria maquiladora y empresas manufactureras. Conocemos la operación desde la planta, no solo desde la teoría, y eso se refleja en cada sistema que construimos.</p>
      </header>
      <div class="cards-grid">
        <article class="card">
          <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></div>
          <h3>Experiencia en planta</h3>
          <p>Parte de nuestro equipo trabajó previamente dentro de la industria automotriz Tier 1, entendiendo manufactura, producción y logística de primera mano — no solo como requerimientos de un cliente.</p>
        </article>
        <article class="card">
          <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m12 2 9 5-9 5-9-5 9-5z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/></svg></div>
          <h3>Visión multidisciplinaria</h3>
          <p>Desarrollo de software, análisis de datos e ingeniería industrial trabajando juntos en un mismo equipo.</p>
        </article>
        <article class="card">
          <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></div>
          <h3>Trato directo</h3>
          <p>Hablas con quien desarrolla tu proyecto, sin intermediarios ni capas de gestión innecesarias.</p>
        </article>
      </div>

      <div class="experience-logos-block">
        <p class="experience-logos-label">Experiencia en manufactura</p>
        <p class="experience-logos-desc">Miembros de nuestro equipo cuentan con experiencia profesional previa dentro de estas empresas de la industria automotriz y de manufactura.</p>
        <div class="experience-logos">
          <div class="experience-logo-card"><img src="<?= ninubo_url('assets/img/customers/aptiv.png') ?>" alt="Aptiv" loading="lazy"></div>
          <div class="experience-logo-card"><img src="<?= ninubo_url('assets/img/customers/delphi.png') ?>" alt="Delphi" loading="lazy"></div>
          <div class="experience-logo-card"><img src="<?= ninubo_url('assets/img/customers/delphi_technologies.png') ?>" alt="Delphi Technologies" loading="lazy"></div>
          <div class="experience-logo-card"><img src="<?= ninubo_url('assets/img/customers/penske.png') ?>" alt="Penske Logistics" loading="lazy"></div>
          <div class="experience-logo-card"><img src="<?= ninubo_url('assets/img/customers/Johnson_Controls.webp') ?>" alt="Johnson Controls" loading="lazy"></div>
          <div class="experience-logo-card"><img src="<?= ninubo_url('assets/img/customers/ametek.png') ?>" alt="Ametek" loading="lazy"></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <div class="page-hero-inner">
        <div class="page-hero-content">
          <p class="section-tag">Por qué NINUBO</p>
          <h2>Experiencia real en manufactura, retail, salud y logística</h2>
          <p class="section-desc">Operamos en el norte de México y sur de Texas. Cada proyecto parte de entender tu operación, no de vender plantillas.</p>
          <div class="stats-strip" style="margin-top: 2rem;">
            <div class="stat-item"><strong>100%</strong><span>A la medida</span></div>
            <div class="stat-item"><strong>24/7</strong><span>Soporte anual</span></div>
            <div class="stat-item"><strong>8+</strong><span>Ciudades</span></div>
            <div class="stat-item"><strong>MX·TX</strong><span>Cobertura</span></div>
          </div>
        </div>
        <div>
          <?php ninubo_img('home-equipo.jpg', 'Equipo profesional NINUBO', 'media-frame', '4/3'); ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Portafolio</p>
        <h2>Proyectos en producción</h2>
        <p class="section-desc">Ejemplos de sistemas web y soluciones de datos que hemos llevado a operación.</p>
      </header>
      <div class="cards-grid">
        <article class="card">
          <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-8"/></svg></div>
          <h3>Dashboard SAP</h3>
          <p>Rastreo en tiempo real de cortos de materia prima conectado al ERP para decisiones inmediatas en planta.</p>
        </article>
        <article class="card">
          <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg></div>
          <h3>Inventario con QR</h3>
          <p>Control de embarques, etiquetas QR y trazabilidad para proveedores nacionales e internacionales.</p>
        </article>
        <article class="card">
          <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
          <h3>POS &amp; Comanda</h3>
          <p>Punto de venta con comanda electrónica para cocina y control de órdenes en restaurantes.</p>
        </article>
      </div>
      <p style="text-align:center;margin-top:2rem;">
        <a href="<?= ninubo_url('portafolio.php') ?>" class="btn btn-ghost">Ver portafolio completo</a>
      </p>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <div class="cta-banner">
        <h2>¿Tienes un proyecto en mente?</h2>
        <p>Cuéntanos tu reto. Te diremos si necesitas un sistema web, análisis de datos, una página web o una combinación de los tres.</p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Contactar a NINUBO</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
