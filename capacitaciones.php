<?php
$pageTitle = 'Capacitaciones | NINUBO';
$pageDesc = 'Talleres presenciales de capacitación técnica para maquiladoras y empresas industriales. Curso disponible: SQL para Consulta y Análisis de Datos.';
$currentPage = 'capacitaciones';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><span>Capacitaciones</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Capacitaciones</p>
        <h1>Talleres presenciales para <span class="text-gradient">fortalecer las habilidades técnicas de tu equipo</span></h1>
        <p class="page-hero-lead">
          Diseñamos capacitaciones prácticas para empresas industriales y maquiladoras, adaptadas al nivel de
          conocimiento de los participantes y a las necesidades reales de su operación.
        </p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Solicitar información</a>
      </div>
      <div><?php ninubo_img('hero-capacitaciones.jpg', 'Capacitación técnica presencial para equipos industriales'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container cards-grid">
      <article class="card">
        <span class="hero-badge" style="margin-bottom:0.9rem;"><span class="badge-dot"></span> Disponible</span>
        <h3>SQL para Consulta y Análisis de Datos</h3>
        <p>Taller presencial para aprender a consultar, relacionar y analizar información almacenada en bases de datos relacionales usando SQL.</p>
        <div class="tag-list">
          <span>Presencial</span><span>Formato modular</span><span>2 a 5 días</span><span>Grupo a tu medida</span>
        </div>
        <a href="<?= ninubo_url('curso-sql.php') ?>" class="btn btn-ghost btn-sm" style="margin-top:1.25rem;">Ver detalle →</a>
      </article>

      <article class="card" style="border-style:dashed; opacity:0.75;">
        <span class="hero-badge" style="margin-bottom:0.9rem;">Próximamente</span>
        <h3>Nuevos talleres en desarrollo</h3>
        <p>Estamos preparando cursos adicionales en temas como Excel avanzado para análisis de datos, Power BI para reportes gerenciales y fundamentos de Business Intelligence.</p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-ghost btn-sm" style="margin-top:1.25rem;">Avísenme cuando estén listos</a>
      </article>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Metodología</p>
        <h2>Un enfoque teórico-práctico en cada taller</h2>
        <p class="section-desc">Todos nuestros cursos combinan fundamentos teóricos con ejercicios y casos de aplicación reales, para que tu equipo desarrolle habilidades que pueda usar de inmediato en su trabajo.</p>
      </header>
      <div class="cards-grid">
        <article class="card">
          <h3>Contenido adaptado</h3>
          <p>El temario se ajusta al nivel de conocimiento previo de los participantes y a las necesidades específicas de tu organización.</p>
        </article>
        <article class="card">
          <h3>Práctica constante</h3>
          <p>Cada módulo incluye demostraciones, ejercicios guiados y casos prácticos con escenarios de negocio reales.</p>
        </article>
        <article class="card">
          <h3>Instructor con experiencia real</h3>
          <p>Impartido por un especialista en tecnología y datos con más de 13 años de experiencia en la industria.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cta-banner">
        <h2>¿Quieres capacitar a tu equipo?</h2>
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
          <summary>¿Qué otros cursos tienen disponibles?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Por el momento el único curso listo para impartirse es "SQL para Consulta y Análisis de Datos". Estamos desarrollando talleres adicionales; si te interesa algún tema en particular, cuéntanos por el formulario de contacto.</p>
        </details>
        <details class="faq-item">
          <summary>¿El contenido se adapta al nivel de nuestro equipo?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Sí. El temario de cada curso puede ajustarse según el nivel de conocimiento previo de los participantes y las necesidades específicas de tu organización.</p>
        </details>
        <details class="faq-item">
          <summary>¿Cuánto cuesta una capacitación?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>La inversión se cotiza según el número de participantes, la duración y el nivel de personalización que requiera tu organización. Contáctanos y te enviamos una propuesta a la medida.</p>
        </details>
        <details class="faq-item">
          <summary>¿Dónde se imparten los talleres?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>De forma presencial en las instalaciones proporcionadas por el cliente, dentro del norte de México y sur de Texas. Para otras ubicaciones, consulta disponibilidad y condiciones de traslado.</p>
        </details>
        <details class="faq-item">
          <summary>¿Cómo empiezo una capacitación con NINUBO?<svg class="faq-arrow" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m2.5 4.5 3.5 3.5 3.5-3.5"/></svg></summary>
          <p>Cuéntanos tu necesidad por el formulario de contacto, WhatsApp o teléfono. Te ayudamos a definir el curso, el número de participantes y las fechas antes de enviarte una propuesta.</p>
        </details>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
