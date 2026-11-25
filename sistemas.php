<?php
$pageTitle = 'Sistemas Web a la medida | NINUBO';
$pageDesc = 'Desarrollo de sistemas web: control de asistencia ZKTECO, CRM, ERP, MEDAPP para consultorios médicos y más soluciones empresariales.';
$currentPage = 'sistemas';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero page-hero--large">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><a href="<?= ninubo_url('servicios.php') ?>">Servicios</a><span>/</span><span>Sistemas Web</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Sistemas Web</p>
        <h1>Software empresarial <span class="text-gradient">hecho para tu operación</span></h1>
        <p class="page-hero-lead">
          Desarrollamos sistemas y aplicaciones web 100% a la medida. Sin instalaciones en cada equipo:
          accede desde cualquier navegador, con datos respaldados, roles de usuario y reportes en tiempo real.
        </p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Cotizar un sistema</a>
      </div>
      <div><?php ninubo_img('hero-sistemas.jpg', 'Panel de sistema web empresarial NINUBO'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Soluciones</p>
        <h2>Los sistemas que más desarrollamos</h2>
        <p class="section-desc">Cada solución se adapta a los procesos de tu empresa. Estos son nuestros desarrollos más solicitados.</p>
      </header>

      <div class="feature-split">
        <div class="feature-split-media"><?php ninubo_img('sistema-asistencia.jpg', 'Sistema de control de asistencia ZKTECO'); ?></div>
        <div class="feature-split-text">
          <h3>Control de Asistencia — Integración ZKTECO</h3>
          <p>Sistema completo de recursos humanos conectado directamente con relojes checadores biométricos ZKTECO. Captura automática de entradas y salidas, gestión de turnos, retardos, faltas e incidencias.</p>
          <ul class="feature-list">
            <li>Sincronización en tiempo real con dispositivos ZKTECO (huella, rostro, tarjeta)</li>
            <li>Reportes de asistencia por empleado, departamento y periodo</li>
            <li>Control de turnos, horarios rotativos y días festivos</li>
            <li>Exportación a nómina y formatos para auditoría</li>
            <li>Panel web accesible para RH y supervisores</li>
          </ul>
          <div class="tag-list"><span>ZKTECO</span><span>Biométrico</span><span>RH</span><span>ADMS/iClock</span></div>
        </div>
      </div>

      <div class="feature-split feature-split--reverse">
        <div class="feature-split-media"><?php ninubo_img('sistema-crm.jpg', 'CRM empresarial a la medida'); ?></div>
        <div class="feature-split-text">
          <h3>CRM — Gestión de Clientes y Ventas</h3>
          <p>Centraliza la información de tus clientes, oportunidades de venta, cotizaciones y seguimiento comercial. Ideal para equipos de ventas que necesitan visibilidad total del pipeline.</p>
          <ul class="feature-list">
            <li>Base de clientes con historial de interacciones y documentos</li>
            <li>Generación de cotizaciones y seguimiento de estatus</li>
            <li>Campañas de correo electrónico y comunicación automatizada</li>
            <li>Reportes de conversión, ventas por vendedor y proyecciones</li>
            <li>Integración con tu sitio web para captura de leads</li>
          </ul>
          <div class="tag-list"><span>CRM</span><span>Ventas</span><span>Cotizaciones</span><span>Email</span></div>
        </div>
      </div>

      <div class="feature-split">
        <div class="feature-split-media"><?php ninubo_img('sistema-erp.jpg', 'ERP modular para empresas'); ?></div>
        <div class="feature-split-text">
          <h3>ERP — Planificación de Recursos Empresariales</h3>
          <p>Sistemas modulares que cubren inventarios, compras, producción, calidad y logística. No vendemos un ERP genérico: construimos los módulos que tu operación realmente utiliza.</p>
          <ul class="feature-list">
            <li>Control de inventarios con entradas, salidas y kardex</li>
            <li>Impresión de etiquetas QR y trazabilidad de embarques</li>
            <li>Gestión de órdenes de compra y proveedores</li>
            <li>Módulos de producción, calidad e ISO</li>
            <li>Dashboards conectados a SAP u otros ERPs existentes</li>
          </ul>
          <div class="tag-list"><span>ERP</span><span>Inventario</span><span>QR</span><span>SAP</span><span>Manufactura</span></div>
        </div>
      </div>

      <div class="feature-split feature-split--reverse">
        <div class="feature-split-media"><?php ninubo_img('sistema-medapp.jpg', 'MEDAPP — Sistema para consultorios médicos'); ?></div>
        <div class="feature-split-text">
          <h3>MEDAPP — Consultorios y Clínicas Médicas</h3>
          <p>Plataforma especializada para doctores y clínicas: expediente clínico electrónico, agenda de citas, recetas digitales y gestión de pacientes. Desarrollada por NINUBO para el sector salud.</p>
          <ul class="feature-list">
            <li>Expediente clínico con historial completo del paciente</li>
            <li>Agenda de citas con recordatorios y control de disponibilidad</li>
            <li>Receta digital y documentación clínica</li>
            <li>Multi-consultorio y multi-usuario con roles</li>
            <li>Cumplimiento de normativas de salud en México</li>
          </ul>
          <div class="tag-list"><span>MEDAPP</span><span>Salud</span><span>Expediente</span><span>Agenda</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Tecnología</p>
        <h2>Características de todos nuestros sistemas</h2>
      </header>
      <div class="cards-grid">
        <article class="card"><h3>Sin instalación</h3><p>Funcionan en el navegador. Tu personal accede desde PC, tablet o celular.</p></article>
        <article class="card"><h3>Datos seguros</h3><p>Respaldos automáticos, control de acceso por roles y conexión cifrada.</p></article>
        <article class="card"><h3>Escalables</h3><p>Crece con tu empresa: agrega módulos, usuarios y sucursales cuando lo necesites.</p></article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cta-banner">
        <h2>¿Necesitas un sistema específico?</h2>
        <p>Si tu caso no encaja en las categorías anteriores, igual lo desarrollamos. Cuéntanos tu proceso.</p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Hablar con un asesor</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
