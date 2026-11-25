<?php
$pageTitle = 'SQL para Consulta y Análisis de Datos | Capacitaciones NINUBO';
$pageDesc = 'Taller presencial y modular para aprender a consultar, relacionar y analizar información con SQL. Se adapta a las necesidades de tu equipo. Ideal para maquiladoras y empresas industriales.';
$currentPage = 'curso-sql';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero page-hero--large">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><a href="<?= ninubo_url('capacitaciones.php') ?>">Capacitaciones</a><span>/</span><span>SQL para Consulta y Análisis de Datos</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Curso disponible</p>
        <h1>SQL para <span class="text-gradient">Consulta y Análisis de Datos</span></h1>
        <p class="page-hero-lead">
          Taller presencial diseñado para fortalecer los conocimientos técnicos de los participantes en el manejo,
          consulta y análisis de información mediante bases de datos relacionales. El contenido se ajusta al nivel
          previo de los asistentes y a las necesidades específicas de la organización.
        </p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Solicitar cotización</a>
      </div>
      <div><?php ninubo_img('hero-curso-sql.jpg', 'Taller presencial de SQL para análisis de datos'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cards-grid">
        <article class="card">
          <h3>Modalidad y duración</h3>
          <p>Presencial, en las instalaciones del cliente. Formato modular que se adapta a tu equipo: la duración típica va de 2 a 5 días, ajustando el alcance de los temas y el número de participantes.</p>
        </article>
        <article class="card">
          <h3>Objetivo</h3>
          <p>Que los participantes comprendan la estructura de una base de datos y utilicen SQL para realizar consultas, filtros, agrupaciones, relaciones entre tablas y análisis de información.</p>
        </article>
        <article class="card">
          <h3>Resultado esperado</h3>
          <p>Un nivel básico sólido a básico-intermedio en SQL, suficiente para resolver necesidades comunes de extracción y análisis de datos en un entorno empresarial.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Temario</p>
        <h2>Programa del curso — estructura modular</h2>
        <p class="section-desc">El contenido se organiza en 4 módulos que se distribuyen según el tiempo disponible de tu equipo — típicamente de 2 a 5 días de taller.</p>
      </header>
      <ol class="process-steps">
        <li class="process-step">
          <span class="step-num">Módulo 1</span>
          <h3>Fundamentos</h3>
          <ul class="feature-list">
            <li>Bases de datos relacionales, tablas y llaves</li>
            <li>Introducción al lenguaje SQL</li>
            <li>SELECT, FROM, DISTINCT, WHERE</li>
            <li>ORDER BY y ejercicios prácticos</li>
          </ul>
        </li>
        <li class="process-step">
          <span class="step-num">Módulo 2</span>
          <h3>Consultas y análisis</h3>
          <ul class="feature-list">
            <li>Funciones de agregación: COUNT, SUM, AVG, MIN, MAX</li>
            <li>GROUP BY, HAVING y operadores lógicos</li>
            <li>CASE WHEN y subconsultas</li>
            <li>Ejercicios de generación de reportes</li>
          </ul>
        </li>
        <li class="process-step">
          <span class="step-num">Módulo 3</span>
          <h3>Relaciones entre tablas</h3>
          <ul class="feature-list">
            <li>INNER JOIN, LEFT JOIN, RIGHT JOIN</li>
            <li>Consultas con múltiples tablas</li>
            <li>Introducción a CTE y vistas (views)</li>
            <li>Casos prácticos de integración de información</li>
          </ul>
        </li>
        <li class="process-step">
          <span class="step-num">Módulo 4</span>
          <h3>Aplicación y consolidación</h3>
          <ul class="feature-list">
            <li>Construcción de consultas completas</li>
            <li>Buenas prácticas e introducción a optimización</li>
            <li>Proyecto práctico integrador</li>
            <li>Evaluación y sesión de dudas</li>
          </ul>
        </li>
      </ol>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="feature-split">
        <div class="feature-split-text">
          <h3>Requerimientos técnicos</h3>
          <p>Cada participante requiere una laptop o computadora con acceso para instalar software de código libre (licencia MIT). La instalación se realiza el primer día del taller.</p>
          <ul class="feature-list">
            <li><strong>MariaDB Server</strong> — motor de base de datos relacional SQL, de código libre</li>
            <li><strong>HeidiSQL</strong> — herramienta de código libre para visualizar y correr consultas SQL</li>
          </ul>
        </div>
        <div class="feature-split-text">
          <h3>Entregables</h3>
          <ul class="feature-list">
            <li>Capacitación presencial con duración adaptada a tu equipo (típicamente 2 a 5 días)</li>
            <li>Material digital de apoyo y ejercicios prácticos</li>
            <li>Scripts y bases de datos de práctica</li>
            <li>Constancia de participación</li>
            <li>Adaptación del contenido al nivel del grupo</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Instructor</p>
        <h2>Capacitación impartida por un especialista en datos</h2>
      </header>
      <div class="cards-grid" style="grid-template-columns:1fr;">
        <article class="card">
          <h3>Sergio J. Ureña — Ingeniero en Sistemas y Productividad Industrial</h3>
          <p>Más de 13 años de experiencia en tecnología, ingeniería de datos, desarrollo de software y administración y análisis de información, incluyendo SQL Server, MySQL, MariaDB, PostgreSQL, procesos ETL, Business Intelligence e integración con SAP.</p>
          <div class="tag-list">
            <span>SQL Server</span><span>MySQL</span><span>MariaDB</span><span>PostgreSQL</span><span>ETL</span><span>Business Intelligence</span><span>SAP</span>
          </div>
        </article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cta-banner">
        <h2>¿Quieres capacitar a tu equipo en SQL?</h2>
        <p>Cuéntanos el número de participantes, el nivel del grupo y las fechas que te interesan, y te enviamos una propuesta a la medida.</p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Solicitar cotización</a>
      </div>
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
          <summary>¿Cuánto dura el curso?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>El taller es modular: normalmente se imparte entre 2 y 5 días, según el alcance de los temas, el nivel del grupo y el tiempo disponible de tu equipo. Definimos la duración exacta al preparar tu propuesta.</p>
        </details>
        <details class="faq-item">
          <summary>¿El contenido se adapta al nivel de nuestro equipo?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Sí. El temario puede ajustarse según el nivel de conocimiento previo de los participantes y las necesidades específicas de tu organización.</p>
        </details>
        <details class="faq-item">
          <summary>¿Qué necesitamos para tomar el curso?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Una laptop o computadora por participante con permisos para instalar software libre, además de un espacio físico adecuado y, en caso de requerirse, acceso a energía eléctrica e internet.</p>
        </details>
        <details class="faq-item">
          <summary>¿Cuánto cuesta este curso?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>La inversión se cotiza según el número de participantes, la duración y el nivel de personalización que requiera tu organización. Contáctanos y te enviamos una propuesta a la medida.</p>
        </details>
        <details class="faq-item">
          <summary>¿Dónde se imparte el taller?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>De forma presencial en las instalaciones proporcionadas por el cliente, dentro del norte de México y sur de Texas. Para otras ubicaciones, consulta disponibilidad y condiciones de traslado.</p>
        </details>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
