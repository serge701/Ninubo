<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle = ninubo_t('Aviso de Privacidad | NINUBO', 'Privacy Notice | NINUBO');
$pageDesc = ninubo_t('Aviso de privacidad de NINUBO — tratamiento de datos personales conforme a la legislación mexicana.', 'NINUBO privacy notice — personal-data processing in accordance with Mexican law.');
$currentPage = 'legal';
include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="page-hero">
    <div class="container">
      <nav class="breadcrumb" aria-label="Ruta">
        <a href="<?= ninubo_url() ?>"><?= ninubo_t('Inicio', 'Home') ?></a><span>/</span><span><?= ninubo_t('Aviso de privacidad', 'Privacy Notice') ?></span>
      </nav>
      <div class="page-hero-content">
        <h1><?= ninubo_t('Aviso de Privacidad', 'Privacy Notice') ?></h1>
        <p class="page-hero-lead"><?= ninubo_t('Última actualización: junio de 2026', 'Last updated: June 2026') ?></p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container legal-content">
      <p><?= ninubo_t(
        '<strong>NINUBO</strong>, con domicilio en Matamoros, Tamaulipas, México, y sitio web <a href="https://ninubo.com">ninubo.com</a>, es responsable del tratamiento de sus datos personales conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP) y su Reglamento.',
        '<strong>NINUBO</strong>, located in Matamoros, Tamaulipas, Mexico, and operating the website <a href="https://ninubo.com">ninubo.com</a>, is responsible for processing your personal data in accordance with Mexico’s Federal Law on the Protection of Personal Data Held by Private Parties (LFPDPPP) and its regulations.'
      ) ?></p>

      <h2><?= ninubo_t('1. Datos personales que recabamos', '1. Personal data we collect') ?></h2>
      <p><?= ninubo_t('Podemos recabar los siguientes datos personales cuando usted nos contacta o utiliza nuestros servicios:', 'We may collect the following personal data when you contact us or use our services:') ?></p>
      <ul>
        <li><?= ninubo_t('Nombre completo', 'Full name') ?></li>
        <li><?= ninubo_t('Correo electrónico', 'Email address') ?></li>
        <li><?= ninubo_t('Número telefónico', 'Phone number') ?></li>
        <li><?= ninubo_t('Nombre de la empresa', 'Company name') ?></li>
        <li><?= ninubo_t('Información proporcionada en formularios de contacto o cotización', 'Information provided in contact or quote forms') ?></li>
      </ul>

      <h2><?= ninubo_t('2. Finalidades del tratamiento', '2. Purposes of processing') ?></h2>
      <p><?= ninubo_t('Sus datos personales serán utilizados para las siguientes finalidades:', 'Your personal data will be used for the following purposes:') ?></p>
      <ul>
        <li><?= ninubo_t('Atender solicitudes de información, cotizaciones y soporte técnico', 'Respond to requests for information, quotes, and technical support') ?></li>
        <li><?= ninubo_t('Prestar los servicios de desarrollo de sistemas, páginas web y análisis de datos contratados', 'Provide the contracted web-system, website, and data-analysis services') ?></li>
        <li><?= ninubo_t('Enviar comunicaciones relacionadas con nuestros servicios', 'Send communications related to our services') ?></li>
        <li><?= ninubo_t('Cumplir obligaciones legales aplicables', 'Comply with applicable legal obligations') ?></li>
      </ul>

      <h2><?= ninubo_t('3. Transferencia de datos', '3. Data transfers') ?></h2>
      <p><?= ninubo_t(
        'NINUBO no transfiere sus datos personales a terceros sin su consentimiento, salvo las excepciones previstas en la LFPDPPP o cuando sea necesario para la prestación del servicio contratado (por ejemplo, proveedores de hosting o infraestructura cloud).',
        'NINUBO does not transfer your personal data to third parties without your consent, except as permitted by the LFPDPPP or when necessary to provide the contracted service (for example, hosting or cloud-infrastructure providers).'
      ) ?></p>

      <h2><?= ninubo_t('4. Derechos ARCO', '4. Your privacy rights') ?></h2>
      <p><?= ninubo_t(
        'Usted tiene derecho a Acceder, Rectificar, Cancelar u Oponerse al tratamiento de sus datos personales (derechos ARCO), así como a revocar el consentimiento otorgado. Para ejercer estos derechos, envíe su solicitud a:',
        'You have the right to access, rectify, cancel, or object to the processing of your personal data (ARCO rights), as well as to withdraw your consent. To exercise these rights, send your request to:'
      ) ?></p>
      <p><a href="mailto:contacto@ninubo.com">contacto@ninubo.com</a></p>

      <h2><?= ninubo_t('5. Medidas de seguridad', '5. Security measures') ?></h2>
      <p><?= ninubo_t('Implementamos medidas técnicas y administrativas para proteger sus datos personales contra daño, pérdida, alteración, destrucción o uso no autorizado. Entre estas medidas se incluyen:', 'We implement technical and administrative measures to protect your personal data from damage, loss, alteration, destruction, or unauthorized use. These measures include:') ?></p>
      <ul>
        <li><?= ninubo_t('Conexión cifrada (SSL/TLS) en nuestro sitio web y en los sistemas que desarrollamos', 'Encrypted connections (SSL/TLS) on our website and on the systems we build') ?></li>
        <li><?= ninubo_t('Respaldos automáticos periódicos de la información', 'Periodic automatic backups of information') ?></li>
        <li><?= ninubo_t('Control de acceso por roles y usuarios, limitando quién puede ver o modificar cada dato', 'Role- and user-based access control, limiting who can view or modify each piece of data') ?></li>
        <li><?= ninubo_t('Separación de la información de cada cliente dentro de nuestros sistemas', 'Separation of each client’s information within our systems') ?></li>
        <li><?= ninubo_t('Revisión y actualización periódica de nuestras prácticas de seguridad', 'Periodic review and update of our security practices') ?></li>
      </ul>
      <p><?= ninubo_t('Estas mismas prácticas aplican a los sistemas, páginas web y soluciones de datos que desarrollamos para nuestros clientes, para proteger tanto su información como la de sus propios usuarios finales.', 'These same practices apply to the systems, websites, and data solutions we build for our clients, to protect both their information and that of their own end users.') ?></p>

      <h2><?= ninubo_t('6. Cookies y tecnologías de rastreo', '6. Cookies and tracking technologies') ?></h2>
      <p><?= ninubo_t(
        'Nuestro sitio web utiliza cookies técnicas necesarias para su funcionamiento. También podemos usar Google Analytics para entender cómo se usa el sitio (páginas visitadas, tiempo de navegación, etc.); estas cookies de análisis solo se activan si usted da su consentimiento en el aviso de cookies que aparece al navegar el sitio, y puede retirarlo en cualquier momento borrando las cookies de su navegador. No utilizamos cookies de rastreo publicitario de terceros.',
        'Our website uses technical cookies required for its operation. We may also use Google Analytics to understand how the site is used (pages visited, time on site, etc.); these analytics cookies are only activated if you consent via the cookie notice shown while browsing the site, and you can withdraw that consent at any time by clearing your browser\'s cookies. We do not use third-party advertising-tracking cookies.'
      ) ?></p>

      <h2><?= ninubo_t('7. Cambios al aviso de privacidad', '7. Changes to this privacy notice') ?></h2>
      <p><?= ninubo_t('NINUBO se reserva el derecho de modificar este aviso de privacidad en cualquier momento. Los cambios serán publicados en esta página con la fecha de actualización correspondiente.', 'NINUBO may update this privacy notice at any time. Changes will be published on this page with the corresponding update date.') ?></p>

      <h2><?= ninubo_t('8. Contacto', '8. Contact') ?></h2>
      <p><?= ninubo_t(
        'Para cualquier duda sobre este aviso de privacidad, contáctenos en <a href="mailto:contacto@ninubo.com">contacto@ninubo.com</a> o al teléfono <a href="tel:+528995083462">8995-08-3462</a>.',
        'For questions about this privacy notice, contact us at <a href="mailto:contacto@ninubo.com">contacto@ninubo.com</a> or call <a href="tel:+528995083462">8995-08-3462</a>.'
      ) ?></p>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
