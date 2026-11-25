<?php
$pageTitle = 'Data Analysis & Business Intelligence | NINUBO';
$pageDesc = 'Análisis de datos, procesos ETL, dashboards Power BI, Tableau e integración con SAP y ERPs para la toma de decisiones empresariales.';
$currentPage = 'data';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero page-hero--large">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><a href="<?= ninubo_url('servicios.php') ?>">Servicios</a><span>/</span><span>Data Analysis</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Data Analysis</p>
        <h1>Convierte tus datos en <span class="text-gradient">decisiones que impulsan resultados</span></h1>
        <p class="page-hero-lead">
          Ayudamos a empresas de todos los tamaños a estructurar, transformar y visualizar su información.
          Desde procesos ETL hasta dashboards ejecutivos conectados a tu ERP — para que dejes de adivinar
          y empieces a decidir con datos reales.
        </p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Agendar consultoría de datos</a>
      </div>
      <div><?php ninubo_img('hero-data.jpg', 'Dashboard de business intelligence'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Nuestro enfoque</p>
        <h2>Del dato crudo a la decisión estratégica</h2>
        <p class="section-desc">No solo hacemos gráficas bonitas. Construimos pipelines de datos confiables que alimentan reportes que tu equipo puede usar todos los días.</p>
      </header>

      <div class="feature-split">
        <div class="feature-split-media"><?php ninubo_img('data-etl.jpg', 'Procesos ETL de extracción y transformación de datos'); ?></div>
        <div class="feature-split-text">
          <h3>Procesos ETL — Extracción, Transformación y Carga</h3>
          <p>Tus datos viven en muchos lugares: ERPs, Excel, bases de datos, APIs y archivos CSV. Nosotros los unificamos en un flujo automatizado y confiable.</p>
          <ul class="feature-list">
            <li>Extracción desde SAP, SQL Server, MySQL, Oracle y archivos planos</li>
            <li>Limpieza, validación y estandarización de datos inconsistentes</li>
            <li>Transformaciones de negocio: cálculos, agregaciones y reglas personalizadas</li>
            <li>Carga programada a data warehouses y modelos analíticos</li>
            <li>Automatización con pipelines que corren sin intervención manual</li>
          </ul>
          <div class="tag-list"><span>ETL</span><span>SQL</span><span>APIs</span><span>Automatización</span></div>
        </div>
      </div>

      <div class="feature-split feature-split--reverse">
        <div class="feature-split-media"><?php ninubo_img('data-bi.jpg', 'Herramientas de business intelligence'); ?></div>
        <div class="feature-split-text">
          <h3>Business Intelligence — Todas las herramientas</h3>
          <p>Trabajamos con las plataformas de BI más utilizadas en la industria. Elegimos la herramienta correcta según tu infraestructura, presupuesto y equipo.</p>
          <ul class="feature-list">
            <li><strong>Power BI</strong> — Dashboards interactivos, DAX y publicación en la nube</li>
            <li><strong>Tableau</strong> — Visualizaciones avanzadas y análisis exploratorio</li>
            <li><strong>Excel avanzado</strong> — Modelos, Power Query, tablas dinámicas y macros</li>
            <li><strong>SQL</strong> — Consultas, vistas, procedimientos almacenados y optimización</li>
            <li>Capacitación para que tu equipo mantenga y evolucione los reportes</li>
          </ul>
          <div class="tag-list"><span>Power BI</span><span>Tableau</span><span>Excel</span><span>SQL</span><span>Python</span></div>
        </div>
      </div>

      <div class="feature-split">
        <div class="feature-split-media"><?php ninubo_img('data-dashboard.jpg', 'Dashboard conectado a ERP SAP'); ?></div>
        <div class="feature-split-text">
          <h3>Integración con ERPs y sistemas empresariales</h3>
          <p>Conectamos tus dashboards y reportes directamente con los sistemas que ya usas. No reemplazamos tu ERP — lo hacemos más visible y útil.</p>
          <ul class="feature-list">
            <li>Conexión con SAP (MM, PP, MDE y módulos personalizados)</li>
            <li>Dashboards en tiempo real para producción, inventario y compras</li>
            <li>Alertas automáticas cuando un KPI sale de rango</li>
            <li>Reportes para gerencia, operaciones, finanzas y calidad</li>
            <li>Análisis de tendencias, proyecciones y escenarios</li>
          </ul>
          <div class="tag-list"><span>SAP</span><span>ERP</span><span>KPIs</span><span>Real-time</span><span>Manufactura</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Para quién</p>
        <h2>Empresas de todos los tamaños</h2>
      </header>
      <div class="cards-grid">
        <article class="card">
          <h3>Manufactura</h3>
          <p>OEE, cortos de materia prima, eficiencia de línea, scrap y tiempos de ciclo conectados a SAP o tu ERP.</p>
        </article>
        <article class="card">
          <h3>Retail &amp; Servicios</h3>
          <p>Ventas por periodo, ticket promedio, rotación de inventario y comportamiento de clientes.</p>
        </article>
        <article class="card">
          <h3>Logística</h3>
          <p>Tiempos de entrega, trazabilidad de embarques, costos de transporte y cumplimiento de SLA.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Herramientas</p>
        <h2>Stack de análisis de datos</h2>
      </header>
      <div class="tech-grid">
        <span>Power BI</span><span>Tableau</span><span>Excel</span><span>SQL</span><span>MySQL</span>
        <span>SAP</span><span>Python</span><span>API REST</span><span>JSON/XML</span><span>ETL</span>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <div class="cta-banner">
        <h2>¿Tus datos están trabajando para ti?</h2>
        <p>Agenda una consultoría y te mostramos qué información de tu empresa puede convertirse en ventaja competitiva.</p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Solicitar consultoría</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
