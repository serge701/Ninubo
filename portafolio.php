<?php
$pageTitle = 'Portafolio | NINUBO';
$pageDesc = 'Proyectos entregados por NINUBO: sistemas web, dashboards SAP, CRM, POS, inventarios y más para empresas en México y Texas.';
$currentPage = 'portafolio';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><span>Portafolio</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Casos reales</p>
        <h1>Proyectos que ya están <span class="text-gradient">en producción</span></h1>
        <p class="page-hero-lead">Cada proyecto nace de un problema concreto del cliente. Estos son algunos de los sistemas que hemos desarrollado e implementado en empresas del norte de México y sur de Texas.</p>
      </div>
      <div><?php ninubo_img('hero-portafolio.jpg', 'Resultados y proyectos reales entregados por NINUBO'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container portfolio-grid">

      <article class="portfolio-item">
        <?php ninubo_img('portfolio-escalaciones.jpg', 'Panel de escalaciones para operaciones de manufactura', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Sistemas Web</span><span>Manufactura</span><span>Operación multi-planta</span></div>
          <h3>Plataforma de escalaciones para operaciones de manufactura</h3>
          <p>Sistema para registrar, asignar y dar seguimiento a incidencias operativas entre plantas, proveedores y niveles de soporte.</p>
          <ul class="portfolio-details">
            <li>Filtros por región, sitio, proveedor, material y nivel de escalación</li>
            <li>Edición y seguimiento de múltiples líneas de trabajo</li>
            <li>Exportación de información para análisis y reuniones operativas</li>
            <li>Visibilidad centralizada para equipos de planta y soporte</li>
          </ul>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('portfolio-requisiciones.jpg', 'Dashboard de requisiciones de materiales sin papel', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Sistemas Web</span><span>Manufactura</span><span>Almacén</span></div>
          <h3>Sistema paperless de requisición de materiales</h3>
          <p>Flujo digital multi-planta que reemplaza formatos en papel para solicitar, aprobar y despachar materiales de almacén.</p>
          <ul class="portfolio-details">
            <li>Catálogo de artículos y carrito de requisición</li>
            <li>Flujo de aprobación y despacho por estatus</li>
            <li>Indicadores de consumo, requisiciones y existencias bajas</li>
            <li>Reportes por planta, área y periodo</li>
          </ul>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('portfolio-erp-manufactura.jpg', 'Dashboard de ERP para fabricación a la medida', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Sistemas Web</span><span>ERP</span><span>Manufactura</span></div>
          <h3>ERP para producción, inventario y personal</h3>
          <p>Plataforma integral para un fabricante a la medida que centraliza proyectos, producción, materiales, herramientas y personal.</p>
          <ul class="portfolio-details">
            <li>Control de clientes, órdenes, proyectos y entregas</li>
            <li>Inventario de materiales, herrajes y herramientas</li>
            <li>Seguimiento de producción y saldos pendientes</li>
            <li>Módulos de asistencia y administración de personal</li>
          </ul>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('portfolio-despacho.jpg', 'Dashboard para la operación de un despacho contable', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Sistemas Web</span><span>Servicios profesionales</span><span>Administración</span></div>
          <h3>Plataforma para administración de despacho contable</h3>
          <p>Sistema que reúne clientes, obligaciones, cobranza y seguimiento mensual en un mismo panel de trabajo.</p>
          <ul class="portfolio-details">
            <li>Directorio de clientes y catálogo de obligaciones</li>
            <li>Seguimiento mensual de cumplimiento y pendientes</li>
            <li>Control de honorarios, cobranza y saldos</li>
            <li>Indicadores para priorizar la operación del despacho</li>
          </ul>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('portfolio-rh-asistencia.jpg', 'Dashboard de recursos humanos y asistencia', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Sistemas Web</span><span>Recursos Humanos</span><span>Asistencia</span></div>
          <h3>Sistema de recursos humanos y control de asistencia</h3>
          <p>Aplicación de autoservicio y gestión de personal para concentrar asistencia, vacaciones, solicitudes y aprobaciones.</p>
          <ul class="portfolio-details">
            <li>Indicadores de asistencia, horas trabajadas y personal activo</li>
            <li>Solicitudes de vacaciones y flujos de aprobación</li>
            <li>Gestión de empleados, departamentos y acciones disciplinarias</li>
            <li>Dashboard operativo para líderes de RH</li>
          </ul>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('genesissoluciones.jpg', 'Sitio web de Genesis Soluciones, especialistas en albercas y sistemas de agua', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Páginas Web</span><span>Albercas y sistemas de agua</span><span>Reynosa</span></div>
          <h3>Sitio web para Genesis Soluciones</h3>
          <p>Página web para una empresa con más de 10 años de experiencia en construcción de albercas, tratamiento de agua, sistemas de bombeo e irrigación en Reynosa y Río Bravo.</p>
          <ul class="portfolio-details">
            <li>Presentación de servicios: construcción de albercas, tratamiento de agua, bombeo e irrigación</li>
            <li>Portafolio de proyectos realizados</li>
            <li>Contacto directo por teléfono, correo y WhatsApp</li>
            <li>Enfocado en clientes residenciales y comerciales</li>
          </ul>
          <a href="https://genesissoluciones.com" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">Ver sitio →</a>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('hmaindustrial.jpg', 'Sitio web de HMA Industrial, proveedor de equipo de protección personal', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Páginas Web</span><span>Equipo de protección personal</span><span>Reynosa</span></div>
          <h3>Sitio web para HMA Industrial</h3>
          <p>Página web para un proveedor de equipo de protección personal e insumos industriales para empresas manufactureras y de la construcción en el noreste de México.</p>
          <ul class="portfolio-details">
            <li>Catálogo de productos: calzado y ropa de seguridad, químicos, lubricantes y más</li>
            <li>Sección de servicios y valores de la empresa</li>
            <li>Presencia orientada a clientes industriales y manufactureros</li>
            <li>Contacto directo por teléfono, correo y dirección física</li>
          </ul>
          <a href="https://hmaindustrial.mx" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">Ver sitio →</a>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('cocinasfranco.jpg', 'Sitio web de Franco Cocinas e Interiores, carpintería fina en Reynosa', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Páginas Web</span><span>Carpintería e interiores</span><span>Reynosa</span></div>
          <h3>Sitio web para Franco Cocinas e Interiores</h3>
          <p>Página web para un taller de carpintería fina especializado en cocinas integrales, closets y muebles 100% a la medida en Reynosa.</p>
          <ul class="portfolio-details">
            <li>Presentación de servicios: cocinas integrales, closets y muebles a la medida</li>
            <li>Galería con más de 38 proyectos realizados</li>
            <li>Proceso de trabajo explicado en 4 pasos, de consulta a instalación</li>
            <li>Contacto directo por teléfono y WhatsApp</li>
          </ul>
          <a href="https://cocinasfranco.com" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">Ver sitio →</a>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('dentaldelcentro.jpg', 'Sitio web de Clínica Dental del Centro en Matamoros', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Páginas Web</span><span>Clínica dental</span><span>Matamoros</span></div>
          <h3>Sitio web para Clínica Dental del Centro</h3>
          <p>Página web para una clínica dental familiar en Matamoros con servicios de ortodoncia, estética dental, implantes y atención de urgencias.</p>
          <ul class="portfolio-details">
            <li>Catálogo de servicios: ortodoncia, estética dental, endodoncia, implantes y más</li>
            <li>Presentación del equipo de doctores</li>
            <li>Sistema de citas en línea</li>
            <li>Contacto directo por teléfono, correo y redes sociales</li>
          </ul>
          <a href="https://dentaldelcentro.mx" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">Ver sitio →</a>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('doctorposadas.jpg', 'Sitio web del Doctor Posadas, cirugía bariátrica', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Páginas Web</span><span>Cirugía bariátrica</span><span>Reynosa</span></div>
          <h3>Sitio web para Doctor Posadas</h3>
          <p>Página web para un cirujano certificado en cirugía bariátrica y metabólica, con más de 100 cirugías realizadas, sirviendo la región de Reynosa y Matamoros.</p>
          <ul class="portfolio-details">
            <li>Presentación de procedimientos: manga gástrica, bypass gástrico y balón intragástrico</li>
            <li>Testimonios de pacientes</li>
            <li>Agendado de citas y consulta inicial gratuita</li>
            <li>Contacto por teléfono, WhatsApp y correo</li>
          </ul>
          <a href="https://doctorposadas.com" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">Ver sitio →</a>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('draerikatrejo.jpg', 'Sitio web de la Dra. Erika Trejo, ginecóloga en Reynosa', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Páginas Web</span><span>Ginecología y obstetricia</span><span>Reynosa</span></div>
          <h3>Sitio web para Dra. Erika Trejo</h3>
          <p>Página web para una ginecóloga certificada con más de 10 años de experiencia en Reynosa, especializada en ginecología, obstetricia y colposcopía.</p>
          <ul class="portfolio-details">
            <li>Presentación de servicios: ginecología, obstetricia, colposcopía y estética íntima</li>
            <li>Galería y sistema de citas en línea</li>
            <li>Información de ubicación y horarios de atención</li>
            <li>Contacto directo por teléfono y correo</li>
          </ul>
          <a href="https://draerikatrejo.com" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">Ver sitio →</a>
        </div>
      </article>

      <article class="portfolio-item">
        <?php ninubo_img('tuiq.jpg', 'Sitio web de TuIQ, plataforma de test de coeficiente intelectual', 'media-frame', '16/10'); ?>
        <div class="portfolio-body">
          <div class="portfolio-meta"><span>Páginas Web</span><span>Herramienta interactiva</span><span>Test psicométrico</span></div>
          <h3>Sitio web para TuIQ</h3>
          <p>Plataforma en línea de test de coeficiente intelectual con 40 preguntas progresivas en cinco áreas cognitivas y resultados inmediatos en la escala estándar de IQ.</p>
          <ul class="portfolio-details">
            <li>Test de 40 preguntas: lógica, numérico, verbal, espacial y memoria</li>
            <li>Resultados inmediatos con desglose por área y percentil</li>
            <li>Sin registro requerido, 100% anónimo</li>
            <li>Modelo freemium: resultados detallados mediante donativo voluntario</li>
          </ul>
          <a href="https://tuiq.mx" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">Ver sitio →</a>
        </div>
      </article>

    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <div class="cta-banner">
        <h2>¿Quieres un proyecto como estos?</h2>
        <p>Cuéntanos tu caso y te proponemos la solución adecuada.</p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Iniciar mi proyecto</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
