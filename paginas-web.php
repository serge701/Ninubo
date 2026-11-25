<?php
$pageTitle = 'Páginas Web a la medida | NINUBO';
$pageDesc = 'Diseño y desarrollo de páginas web profesionales a la medida para empresas. Presencia digital, hosting, dominio y branding.';
$currentPage = 'web';
require __DIR__ . '/includes/image.php';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero page-hero--large">
    <div class="container page-hero-inner">
      <div class="page-hero-content">
        <nav class="breadcrumb" aria-label="Ruta">
          <a href="<?= ninubo_url() ?>">Inicio</a><span>/</span><a href="<?= ninubo_url('servicios.php') ?>">Servicios</a><span>/</span><span>Páginas Web</span>
        </nav>
        <p class="hero-badge"><span class="badge-dot"></span> Páginas Web</p>
        <h1>Tu negocio merece una <span class="text-gradient">presencia web profesional</span></h1>
        <p class="page-hero-lead">
          Diseñamos y desarrollamos páginas web a la medida para que tu empresa se vea, comunique y convierta
          en internet. No usamos plantillas genéricas: cada sitio refleja la identidad y objetivos de tu marca.
        </p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Cotizar mi sitio web</a>
      </div>
      <div><?php ninubo_img('hero-web.jpg', 'Diseño de página web profesional'); ?></div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Qué incluye</p>
        <h2>Desde el diseño hasta el lanzamiento</h2>
        <p class="section-desc">Te acompañamos en todo el proceso para que tu negocio tenga presencia sólida en la web.</p>
      </header>

      <div class="feature-split">
        <div class="feature-split-media"><?php ninubo_img('web-diseno.jpg', 'Diseño UI/UX de sitio web'); ?></div>
        <div class="feature-split-text">
          <h3>Diseño UI/UX a la medida</h3>
          <p>Creamos la experiencia visual de tu sitio pensando en tu cliente ideal: navegación clara, jerarquía de información y diseño que transmite confianza y profesionalismo.</p>
          <ul class="feature-list">
            <li>Wireframes y prototipos antes del desarrollo</li>
            <li>Paleta de colores, tipografía e identidad visual</li>
            <li>Diseño responsive: se ve perfecto en móvil, tablet y desktop</li>
            <li>Optimización de velocidad y experiencia de usuario</li>
          </ul>
        </div>
      </div>

      <div class="feature-split feature-split--reverse">
        <div class="feature-split-media"><?php ninubo_img('web-corporativo.jpg', 'Sitio web corporativo empresarial'); ?></div>
        <div class="feature-split-text">
          <h3>Sitios corporativos y landing pages</h3>
          <p>Desarrollamos desde sitios institucionales completos hasta landing pages enfocadas en captar leads o promover un servicio específico.</p>
          <ul class="feature-list">
            <li>Sitios corporativos multi-sección (servicios, nosotros, contacto, blog)</li>
            <li>Landing pages para campañas y promociones</li>
            <li>Catálogos de productos y portafolios de servicios</li>
            <li>Formularios de contacto integrados con correo</li>
            <li>Integración con WhatsApp, redes sociales y mapas</li>
          </ul>
        </div>
      </div>

      <div class="feature-split">
        <div class="feature-split-media"><?php ninubo_img('web-ecommerce.jpg', 'Tienda en línea y presencia digital'); ?></div>
        <div class="feature-split-text">
          <h3>Infraestructura y presencia digital</h3>
          <p>Más allá del diseño, te ayudamos con todo lo que tu negocio necesita para operar en internet de forma profesional.</p>
          <ul class="feature-list">
            <li>Registro y configuración de dominio (.com, .mx, etc.)</li>
            <li>Hosting en servidores profesionales con SSL</li>
            <li>Cuentas de correo electrónico con tu dominio</li>
            <li>Logotipos, slogans y fotografía corporativa</li>
            <li>Videos explicativos y contenido para redes sociales</li>
          </ul>
          <div class="tag-list"><span>Hosting</span><span>Dominio</span><span>SSL</span><span>Branding</span><span>SEO básico</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section section--alt">
    <div class="container">
      <header class="section-header section-header--center">
        <p class="section-tag">Tipos de sitio</p>
        <h2>¿Qué tipo de página necesitas?</h2>
      </header>
      <div class="cards-grid">
        <article class="card"><h3>Corporativo</h3><p>Presencia institucional completa para empresas establecidas que quieren proyectar seriedad y confianza.</p></article>
        <article class="card"><h3>Landing Page</h3><p>Página enfocada en un solo objetivo: captar leads, promover un evento o lanzar un producto.</p></article>
        <article class="card"><h3>Portafolio / Servicios</h3><p>Ideal para profesionales independientes, consultores y negocios de servicios.</p></article>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cta-banner">
        <h2>Hagamos visible tu negocio en internet</h2>
        <p>Cuéntanos qué tipo de sitio necesitas y te enviamos una propuesta personalizada.</p>
        <a href="<?= ninubo_url('contacto.php') ?>" class="btn btn-primary">Solicitar propuesta</a>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
