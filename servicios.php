<?php
$pageTitle = 'Servicios | NINUBO';
$pageDesc = 'Los tres servicios de NINUBO: sistemas web a la medida, análisis de datos y páginas web profesionales para empresas.';
$currentPage = 'servicios';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><span>Servicios</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Lo que hacemos</p>
        <h1>Tres servicios. <span class="text-gradient">Un socio digital para tu empresa.</span></h1>
        <p class="page-hero-lead">NINUBO concentra su experiencia en tres áreas: sistemas web para operar mejor, análisis de datos para decidir mejor y páginas web para crecer en internet.</p>
      </div>
      <div><?php ninubo_img('hero-servicios.jpg', 'Los tres servicios digitales de NINUBO'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container cards-grid">
      <article class="card">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></div>
        <h3>Sistemas Web</h3>
        <p>Herramientas a la medida para automatizar procesos: CRM, ERP, control de asistencia ZKTECO, MEDAPP y flujos específicos de tu operación.</p>
        <a href="<?= ninubo_url('sistemas.php') ?>" class="btn btn-ghost btn-sm" style="margin-top:1rem;">Ver detalle →</a>
      </article>
      <article class="card">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg></div>
        <h3>Páginas Web</h3>
        <p>Diseño y desarrollo de sitios profesionales que explican lo que haces, refuerzan tu marca y facilitan que te contacten.</p>
        <a href="<?= ninubo_url('paginas-web.php') ?>" class="btn btn-ghost btn-sm" style="margin-top:1rem;">Ver detalle →</a>
      </article>
      <article class="card">
        <div class="card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-8"/></svg></div>
        <h3>Data Analysis</h3>
        <p>ETL, dashboards de BI e integración con ERPs para transformar datos dispersos en indicadores útiles para tu equipo.</p>
        <a href="<?= ninubo_url('data-analysis.php') ?>" class="btn btn-ghost btn-sm" style="margin-top:1rem;">Ver detalle →</a>
      </article>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Stack tecnológico</p>
        <h2>Tecnologías con las que trabajamos</h2>
        <p class="section-desc">Elegimos la herramienta correcta para cada proyecto, no solo la que ya conocemos. Este es el stack real que respalda nuestras tres áreas.</p>
      </header>

      <div class="tech-categories">
        <article class="tech-category tech-category--systems">
          <div class="tech-category-head">
            <div class="tech-category-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></div>
            <div>
              <h3>Sistemas Web</h3>
              <p>Lenguajes y herramientas para construir software empresarial</p>
            </div>
          </div>
          <div class="tech-pills">
            <span class="tech-pill"><span class="tech-pill-dot"></span>PHP</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>MySQL</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>MariaDB</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>JavaScript</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>jQuery</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Bootstrap</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>REST API</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>ZKTECO (Biometría)</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Integración SAP</span>
          </div>
        </article>

        <article class="tech-category tech-category--data">
          <div class="tech-category-head">
            <div class="tech-category-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3v18h18"/><path d="M7 16l4-6 4 3 5-8"/></svg></div>
            <div>
              <h3>Data Analysis</h3>
              <p>Plataformas de BI y bases de datos para decidir con información</p>
            </div>
          </div>
          <div class="tech-pills">
            <span class="tech-pill"><span class="tech-pill-dot"></span>Power BI</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Tableau</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Excel Avanzado</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>SQL</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>SQL Server</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>MySQL</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>MariaDB</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Python</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>SAP</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>ETL</span>
          </div>
        </article>

        <article class="tech-category tech-category--web">
          <div class="tech-category-head">
            <div class="tech-category-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg></div>
            <div>
              <h3>Páginas Web</h3>
              <p>Todo lo necesario para diseñar, construir y publicar tu sitio</p>
            </div>
          </div>
          <div class="tech-pills">
            <span class="tech-pill"><span class="tech-pill-dot"></span>HTML5</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>CSS3</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>JavaScript</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>PHP</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Diseño Responsive</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>SEO Técnico</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Google Analytics</span>
            <span class="tech-pill"><span class="tech-pill-dot"></span>Hosting &amp; SSL</span>
          </div>
        </article>
      </div>

      <div class="tech-protocols">
        <p class="tech-protocols-label">Protocolos y estándares</p>
        <div class="tech-grid">
          <span>HTTP/HTTPS</span><span>TCP/IP</span><span>REST</span><span>SOAP</span>
          <span>FTP/SFTP</span><span>SMTP</span><span>SSL/TLS</span><span>JSON/XML</span>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Compromiso</p>
        <h2>Garantía y soporte que respaldan cada&nbsp;proyecto</h2>
      </header>
      <div class="guarantee-grid">
        <article class="card">
          <div class="card-icon card-icon--success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2 4 5v6c0 5 3.5 9 8 11 4.5-2 8-6 8-11V5z"/><path d="m9 12 2 2 4-4"/></svg></div>
          <h3>Garantía de satisfacción</h3>
          <p>No cobramos hasta que estés satisfecho con el resultado y se hayan entregado completos y funcionando todos los "deliverables" acordados en el proyecto.</p>
        </article>
        <article class="card">
          <div class="card-icon card-icon--success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg></div>
          <h3>Soporte 24/7</h3>
          <p>Te acompañamos los 365 días del año por correo prioritario, WhatsApp y llamada telefónica — siempre hay alguien disponible cuando lo necesitas.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Metodología</p>
        <h2>Cómo convertimos una necesidad en una solución útil</h2>
      </header>
      <ol class="process-steps">
        <li class="process-step"><span class="step-num">01</span><h3>Diagnóstico</h3><p>Analizamos tu operación, procesos y objetivos.</p></li>
        <li class="process-step"><span class="step-num">02</span><h3>Diseño</h3><p>Prototipos y arquitectura acordados contigo.</p></li>
        <li class="process-step"><span class="step-num">03</span><h3>Desarrollo</h3><p>Entregas iterativas con pruebas en cada fase.</p></li>
        <li class="process-step"><span class="step-num">04</span><h3>Soporte</h3><p>Capacitación y acompañamiento 24/7 los 365 días.</p></li>
      </ol>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Preguntas frecuentes</p>
        <h2>Resolviendo tus dudas más comunes</h2>
      </header>
      <div class="faq-list">
        <details class="faq-item">
          <summary>¿Cuánto tiempo toma desarrollar un sistema a la medida?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Depende del alcance: proyectos pequeños o landing pages pueden tomar pocas semanas; sistemas medianos como un CRM o control de asistencia, de 4 a 10 semanas; ERPs completos y proyectos de mayor alcance pueden tomar varios meses. Al inicio del proyecto te damos un estimado claro de tiempos.</p>
        </details>
        <details class="faq-item">
          <summary>¿Trabajan solo con empresas del norte de México y sur de Texas?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Esa es nuestra zona de mayor presencia y donde realizamos visitas en sitio, pero trabajamos de forma remota con empresas en cualquier ubicación.</p>
        </details>
        <details class="faq-item">
          <summary>¿Cómo funciona el pago y la garantía?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>No cobramos hasta que estés satisfecho con el resultado y se hayan entregado completos y funcionando todos los "deliverables" acordados en el proyecto.</p>
        </details>
        <details class="faq-item">
          <summary>¿Qué incluye el soporte 24/7?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Soporte disponible los 365 días del año por correo prioritario, WhatsApp y llamada telefónica, para resolver dudas, incidencias o solicitudes sobre tu sistema.</p>
        </details>
        <details class="faq-item">
          <summary>¿Puedo agregar funciones o módulos después de que el sistema esté en producción?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Sí. Todos nuestros sistemas están diseñados para escalar: puedes agregar módulos, usuarios y sucursales conforme tu empresa lo necesite.</p>
        </details>
        <details class="faq-item">
          <summary>¿Qué tan segura está la información de mi empresa?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p><?= ninubo_t(
            'Tus datos se manejan con respaldos automáticos, control de acceso por roles y conexión cifrada, conforme a lo descrito en nuestro <a href="' . ninubo_url('aviso-de-privacidad.php') . '">Aviso de Privacidad</a>.',
            'Your data is handled with automatic backups, role-based access control, and encrypted connections, as described in our <a href="' . ninubo_url('aviso-de-privacidad.php') . '">Privacy Notice</a>.'
          ) ?></p>
        </details>
        <details class="faq-item">
          <summary>¿Cómo empiezo un proyecto con NINUBO?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Cuéntanos tu necesidad por el formulario de contacto, WhatsApp o teléfono. Agendamos un diagnóstico inicial sin costo y te enviamos una propuesta con alcance, tiempos y costo antes de comenzar.</p>
        </details>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
