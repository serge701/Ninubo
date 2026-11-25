<?php
$pageTitle = 'Contacto | NINUBO';
$pageDesc = 'Contacta a NINUBO para cotizar sistemas web, páginas web o análisis de datos. contacto@ninubo.com — 8995-08-3462.';
$currentPage = 'contacto';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
$_form_token = ninubo_generate_token();
?>

<main>
  <section class="page-hero">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><span>Contacto</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Hablemos</p>
        <h1>Cuéntanos qué necesita <span class="text-gradient">tu empresa</span></h1>
        <p class="page-hero-lead">Cuéntanos tu reto y te orientamos hacia el servicio correcto: sistemas web, análisis de datos o páginas web. Respondemos por correo, teléfono o WhatsApp.</p>
      </div>
      <div><?php ninubo_img('hero-contacto.jpg', 'Equipo de NINUBO listo para atender tu proyecto'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container contact-layout">
      <div class="contact-info-card">
        <h2>Datos de contacto</h2>
        <p style="color:var(--color-text-muted);margin-top:0.5rem;">Estamos en el norte de México y damos servicio también al sur de Texas.</p>
        <ul class="contact-details">
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            <a href="mailto:contacto@ninubo.com">contacto@ninubo.com</a>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <a href="tel:+528995083462">8995-08-3462</a>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg>
            <a href="https://ninubo.com" target="_blank" rel="noopener">ninubo.com</a>
          </li>
        </ul>
        <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--color-border);">
          <p class="section-tag">Horario de respuesta</p>
          <p style="color:var(--color-text-muted);font-size:0.92rem;">Soporte técnico 24/7. Consultas comerciales respondemos en menos de 24 horas hábiles.</p>
        </div>
      </div>

      <div class="contact-form-card">
        <h2>Enviar mensaje</h2>
        <form class="contact-form" novalidate>
          <div class="form-alert" role="alert"></div>
          <div class="form-row">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required autocomplete="name">
          </div>
          <div class="form-row">
            <label for="email">Correo electrónico *</label>
            <input type="email" id="email" name="email" placeholder="tu@empresa.com" required autocomplete="email">
          </div>
          <div class="form-row">
            <label for="empresa">Empresa</label>
            <input type="text" id="empresa" name="empresa" placeholder="Nombre de tu empresa" autocomplete="organization">
          </div>
          <div class="form-row">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" placeholder="8995-08-3462" autocomplete="tel">
          </div>
          <div class="form-row">
            <label for="asunto">¿Qué servicio te interesa?</label>
            <select id="asunto" name="asunto">
              <option value="Consulta general">No estoy seguro / quiero orientación</option>
              <option value="Sistemas Web">Sistemas web a la medida</option>
              <option value="Control de Asistencia ZKTECO">Control de Asistencia ZKTECO</option>
              <option value="CRM / ERP">CRM / ERP</option>
              <option value="MEDAPP — Consultorio médico">MEDAPP — Consultorio médico</option>
              <option value="Páginas Web">Páginas web profesionales</option>
              <option value="Data Analysis / BI">Análisis de datos y BI</option>
            </select>
          </div>
          <div class="form-row">
            <label for="mensaje">Mensaje *</label>
            <textarea id="mensaje" name="mensaje" placeholder="Cuéntanos qué necesitas, cuántos usuarios, plazos, etc." required></textarea>
          </div>
          <!-- Honeypot: invisible para humanos, los bots lo llenan -->
          <div class="hp-field" aria-hidden="true">
            <label for="hp_url">URL</label>
            <input type="text" id="hp_url" name="hp_url" tabindex="-1" autocomplete="off">
          </div>
          <input type="hidden" name="_t" value="<?= htmlspecialchars($_form_token) ?>">
          <button type="submit" class="btn btn-primary btn-full">Enviar mensaje</button>
        </form>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
