<?php
/**
 * Esquema completo del "brief" inicial de cliente (documento original en 24
 * secciones, agrupadas aquí en 13 pasos temáticos para el wizard).
 *
 * Este arreglo es la única fuente de verdad: lo usan brief.php (formulario
 * público), api/brief-guardar.php (para saber qué campos aceptar) y
 * admin/brief-ver.php (para mostrar las respuestas). Si se necesita agregar
 * o cambiar una pregunta, se edita solo aquí.
 *
 * El formulario público es bilingüe (ES/EN) — ver [[project-client-brief]].
 * Cada texto visible (label, hint, item_label, título, opciones) se define
 * con ninubo_brief_bt('Español', 'English') y ninubo_brief_schema($lang) lo
 * resuelve al idioma pedido. Las opciones de checkboxes/radio usan una
 * clave estable en minúsculas (p.ej. 'nuevos_clientes') como valor real
 * guardado en la base de datos — así la respuesta no cambia según el idioma
 * en que el cliente llenó el formulario, y el panel admin (siempre en
 * español) puede mostrar la etiqueta correcta sin importar cuál se usó.
 *
 * Tipos de campo soportados:
 *   text, tel, email, textarea  -> valor simple string
 *   checkboxes                  -> array de claves marcadas (+ "_otro" si other=true)
 *   radio                       -> valor simple string (una clave), elegido entre 'options'
 *   repeat                      -> lista numerada de sub-formularios (p.ej. 3 servicios)
 *   list                        -> lista numerada de un solo campo (p.ej. 5 referencias)
 *   schedule                    -> un campo de texto por día de la semana
 */

/** Envuelve un texto bilingüe. ninubo_brief_schema() lo resuelve al idioma pedido. */
function ninubo_brief_bt(string $es, string $en): array {
    return ['es' => $es, 'en' => $en];
}

function ninubo_brief_resolve_text($value, string $lang): string {
    if (is_array($value)) {
        return $value[$lang] ?? ($value['es'] ?? '');
    }
    return (string)$value;
}

function ninubo_brief_resolve_field(array $field, string $lang): array {
    $out = $field;

    foreach (['label', 'hint', 'item_label'] as $key) {
        if (isset($field[$key])) {
            $out[$key] = ninubo_brief_resolve_text($field[$key], $lang);
        }
    }

    if (isset($field['options']) && is_array($field['options'])) {
        $resolved = [];
        foreach ($field['options'] as $optKey => $optLabel) {
            $resolved[$optKey] = ninubo_brief_resolve_text($optLabel, $lang);
        }
        $out['options'] = $resolved;
    }

    if (isset($field['days']) && is_array($field['days'])) {
        $resolved = [];
        foreach ($field['days'] as $dayKey => $dayLabel) {
            $resolved[$dayKey] = ninubo_brief_resolve_text($dayLabel, $lang);
        }
        $out['days'] = $resolved;
    }

    if (isset($field['subfields']) && is_array($field['subfields'])) {
        $resolved = [];
        foreach ($field['subfields'] as $subName => $subField) {
            $resolved[$subName] = ninubo_brief_resolve_field($subField, $lang);
        }
        $out['subfields'] = $resolved;
    }

    return $out;
}

function ninubo_brief_schema_raw(): array {
    $bt = 'ninubo_brief_bt';

    return [
        'negocio' => [
            'title' => $bt('Tu negocio', 'Your business'),
            'desc' => $bt('Información general de contacto y a qué se dedica tu empresa.', 'General contact information and what your business does.'),
            'fields' => [
                'nombre_comercial' => ['type' => 'text', 'label' => $bt('Nombre comercial', 'Business name'), 'required' => true],
                'nombre_legal' => ['type' => 'text', 'label' => $bt('Nombre legal de la empresa (si aplica)', 'Legal company name (if applicable)')],
                'anio_inicio' => ['type' => 'text', 'label' => $bt('Año en que inició el negocio', 'Year the business started')],
                'contacto_nombre' => ['type' => 'text', 'label' => $bt('Nombre del propietario o persona de contacto', 'Owner or contact person name'), 'required' => true],
                'telefono' => ['type' => 'tel', 'label' => $bt('Teléfono', 'Phone')],
                'whatsapp' => ['type' => 'tel', 'label' => $bt('WhatsApp', 'WhatsApp')],
                'email' => ['type' => 'email', 'label' => $bt('Correo electrónico', 'Email address'), 'required' => true],
                'direccion' => ['type' => 'text', 'label' => $bt('Dirección física', 'Physical address')],
                'ciudad_estado' => ['type' => 'text', 'label' => $bt('Ciudad / Estado', 'City / State')],
                'horario_atencion' => ['type' => 'text', 'label' => $bt('Horario de atención', 'Business hours')],
                'area_geografica' => ['type' => 'textarea', 'label' => $bt('Área geográfica donde ofrece sus servicios', 'Geographic area you serve'), 'hint' => $bt('Ejemplo: McAllen, Mission, Edinburg, Pharr, Weslaco, Brownsville, todo el Rio Grande Valley, etc.', 'Example: McAllen, Mission, Edinburg, Pharr, Weslaco, Brownsville, the whole Rio Grande Valley, etc.')],
                'descripcion_negocio' => ['type' => 'textarea', 'label' => $bt('¿A qué se dedica su empresa?', 'What does your business do?'), 'hint' => $bt('Describa brevemente qué hace su negocio y qué productos o servicios ofrece.', 'Briefly describe what your business does and which products or services you offer.')],
                'objetivo_web' => ['type' => 'checkboxes', 'label' => $bt('¿Cuál es el principal objetivo de su página web?', 'What is the main goal of your website?'), 'other' => true, 'options' => [
                    'nuevos_clientes' => $bt('Conseguir nuevos clientes', 'Get new customers'),
                    'llamadas' => $bt('Recibir llamadas', 'Get phone calls'),
                    'whatsapp_msj' => $bt('Recibir mensajes por WhatsApp', 'Get WhatsApp messages'),
                    'cotizaciones' => $bt('Generar solicitudes de cotización', 'Generate quote requests'),
                    'mostrar_servicios' => $bt('Mostrar nuestros servicios', 'Showcase our services'),
                    'mostrar_portafolio' => $bt('Mostrar nuestro portafolio', 'Showcase our portfolio'),
                    'vender_productos' => $bt('Vender productos', 'Sell products'),
                    'reservar_citas' => $bt('Reservar citas', 'Book appointments'),
                    'info_negocio' => $bt('Dar información sobre nuestro negocio', 'Provide information about our business'),
                    'mejorar_google' => $bt('Mejorar nuestra presencia en Google', 'Improve our Google presence'),
                ]],
            ],
        ],

        'cliente_ideal' => [
            'title' => $bt('Cliente ideal', 'Ideal customer'),
            'desc' => $bt('A quién le hablas y qué te diferencia de la competencia.', 'Who you\'re speaking to and what sets you apart from the competition.'),
            'fields' => [
                'cliente_ideal' => ['type' => 'textarea', 'label' => $bt('¿Quién es su cliente ideal?', 'Who is your ideal customer?'), 'hint' => $bt('Describa el tipo de persona o empresa que normalmente contrata sus servicios.', 'Describe the type of person or company that typically hires your services.')],
                'areas_atender' => ['type' => 'textarea', 'label' => $bt('¿Qué ciudades o áreas desea atender?', 'Which cities or areas do you want to serve?')],
                'diferenciador' => ['type' => 'textarea', 'label' => $bt('¿Qué diferencia a su negocio de la competencia?', 'What sets your business apart from the competition?'), 'hint' => $bt('Por ejemplo: experiencia, precio, calidad, rapidez, garantía, atención personalizada, experiencia bilingüe, servicio 24/7, productos especializados.', 'For example: experience, price, quality, speed, warranty, personalized attention, bilingual service, 24/7 service, specialized products.')],
            ],
        ],

        'servicios' => [
            'title' => $bt('Servicios / productos', 'Services / products'),
            'desc' => $bt('Indique todos los servicios o productos que desea mostrar en la página.', 'List all the services or products you want to show on the site.'),
            'fields' => [
                'servicios' => ['type' => 'repeat', 'item_label' => $bt('Servicio / Producto', 'Service / Product'), 'count' => 3, 'subfields' => [
                    'nombre' => ['type' => 'text', 'label' => $bt('Nombre', 'Name')],
                    'descripcion' => ['type' => 'textarea', 'label' => $bt('Descripción', 'Description')],
                    'precio' => ['type' => 'text', 'label' => $bt('Precio (si desea mostrarlo)', 'Price (if you want to show it)')],
                ]],
                'servicios_adicionales' => ['type' => 'textarea', 'label' => $bt('Servicios adicionales', 'Additional services'), 'hint' => $bt('Agregue todos los que sean necesarios.', 'Add as many as needed.')],
            ],
        ],

        'estructura_sitio' => [
            'title' => $bt('Páginas del sitio', 'Site pages'),
            'desc' => $bt('Indique qué páginas desea incluir.', 'Indicate which pages you want to include.'),
            'fields' => [
                'paginas_sitio' => ['type' => 'checkboxes', 'label' => $bt('Páginas a incluir', 'Pages to include'), 'other' => true, 'options' => [
                    'home' => $bt('Home / Inicio', 'Home'),
                    'about' => $bt('About Us / Nosotros', 'About Us'),
                    'services' => $bt('Services / Servicios', 'Services'),
                    'service_pages' => $bt('Individual Service Pages', 'Individual Service Pages'),
                    'products' => $bt('Products / Productos', 'Products'),
                    'gallery' => $bt('Gallery / Galería', 'Gallery'),
                    'portfolio' => $bt('Portfolio / Proyectos', 'Portfolio'),
                    'reviews' => $bt('Reviews / Testimonios', 'Reviews'),
                    'faq' => $bt('FAQ / Preguntas frecuentes', 'FAQ'),
                    'blog' => $bt('Blog / Noticias', 'Blog'),
                    'contact' => $bt('Contact / Contacto', 'Contact'),
                    'booking' => $bt('Booking / Reservaciones', 'Booking'),
                ]],
            ],
        ],

        'inicio_nosotros' => [
            'title' => $bt('Inicio y Nosotros', 'Home and About'),
            'desc' => $bt('El mensaje principal de tu página de inicio y la historia de tu empresa.', 'The main message on your homepage and your company\'s story.'),
            'fields' => [
                'titulo_principal' => ['type' => 'text', 'label' => $bt('¿Qué mensaje desea que vea primero el visitante?', 'What message do you want visitors to see first?'), 'hint' => $bt('Ejemplo: "Professional Roofing Services in the Rio Grande Valley". Si no tiene una frase definida, podemos proponer opciones.', 'Example: "Professional Roofing Services in the Rio Grande Valley". If you don\'t have a set phrase, we can propose options.')],
                'subtitulo' => ['type' => 'textarea', 'label' => $bt('Subtítulo', 'Subtitle'), 'hint' => $bt('Explique brevemente qué ofrece y por qué deberían contratarlo.', 'Briefly explain what you offer and why visitors should hire you.')],
                'cta_principal' => ['type' => 'radio', 'label' => $bt('¿Qué quiere que haga el visitante? (llamada a la acción principal)', 'What do you want the visitor to do? (main call to action)'), 'other' => true, 'options' => [
                    'call_now' => $bt('Call Now', 'Call Now'),
                    'free_quote' => $bt('Get a Free Quote', 'Get a Free Quote'),
                    'contact_us' => $bt('Contact Us', 'Contact Us'),
                    'schedule_appt' => $bt('Schedule an Appointment', 'Schedule an Appointment'),
                    'send_message' => $bt('Send a Message', 'Send a Message'),
                    'view_services' => $bt('View Our Services', 'View Our Services'),
                ]],
                'historia_empresa' => ['type' => 'textarea', 'label' => $bt('Historia de la empresa', 'Company history'), 'hint' => $bt('Cuéntenos brevemente cómo inició el negocio.', 'Briefly tell us how the business started.')],
                'anos_experiencia' => ['type' => 'text', 'label' => $bt('¿Cuántos años de experiencia tiene?', 'How many years of experience do you have?')],
                'mision_filosofia' => ['type' => 'textarea', 'label' => $bt('Misión / filosofía', 'Mission / philosophy'), 'hint' => $bt('¿Qué representa su empresa?', 'What does your company stand for?')],
                'diferencia_empresa' => ['type' => 'textarea', 'label' => $bt('¿Qué hace diferente a su empresa?', 'What makes your company different?')],
            ],
        ],

        'visual_redes' => [
            'title' => $bt('Material visual y redes', 'Visual assets and social media'),
            'desc' => $bt('Fotos, logo y tus redes sociales.', 'Photos, logo, and your social media accounts.'),
            'fields' => [
                'material_visual' => ['type' => 'checkboxes', 'label' => $bt('Indique qué materiales tiene disponibles', 'Indicate which materials you have available'), 'options' => [
                    'logo' => $bt('Logotipo', 'Logo'),
                    'fotos_negocio' => $bt('Fotografías del negocio', 'Photos of the business'),
                    'fotos_trabajos' => $bt('Fotografías de trabajos realizados', 'Photos of completed work'),
                    'fotos_productos' => $bt('Fotografías de productos', 'Product photos'),
                    'fotos_equipo' => $bt('Fotografías del equipo/personas', 'Photos of the team/people'),
                    'videos' => $bt('Videos', 'Videos'),
                    'material_publicitario' => $bt('Material publicitario', 'Advertising materials'),
                    'brand_guidelines' => $bt('Manual de identidad / Brand Guidelines', 'Brand guidelines'),
                ]],
                'archivos_link' => ['type' => 'text', 'label' => $bt('Link a sus archivos', 'Link to your files'), 'hint' => $bt('Suba sus fotos, logo y videos (en la mejor calidad posible) a una carpeta de Google Drive, WeTransfer o Dropbox y pegue aquí el link.', 'Upload your photos, logo, and videos (in the best quality available) to a Google Drive, WeTransfer, or Dropbox folder and paste the link here.')],
                'red_facebook' => ['type' => 'text', 'label' => $bt('Facebook', 'Facebook')],
                'red_instagram' => ['type' => 'text', 'label' => $bt('Instagram', 'Instagram')],
                'red_tiktok' => ['type' => 'text', 'label' => $bt('TikTok', 'TikTok')],
                'red_youtube' => ['type' => 'text', 'label' => $bt('YouTube', 'YouTube')],
                'red_linkedin' => ['type' => 'text', 'label' => $bt('LinkedIn', 'LinkedIn')],
                'red_otra' => ['type' => 'text', 'label' => $bt('Otra red social', 'Other social network')],
            ],
        ],

        'google_reviews' => [
            'title' => $bt('Google y reseñas', 'Google and reviews'),
            'desc' => $bt('Presencia en Google, testimonios y palabras clave.', 'Google presence, testimonials, and keywords.'),
            'fields' => [
                'google_business' => ['type' => 'radio', 'label' => $bt('¿Su negocio ya aparece en Google Maps?', 'Does your business already appear on Google Maps?'), 'options' => [
                    'si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No'), 'no_seguro' => $bt('No estoy seguro', 'Not sure'),
                ]],
                'google_maps_link' => ['type' => 'text', 'label' => $bt('Link de Google Maps / Google Business Profile', 'Google Maps / Google Business Profile link')],
                'mostrar_resenas' => ['type' => 'radio', 'label' => $bt('¿Desea mostrar reseñas de clientes en la página?', 'Do you want to show customer reviews on the site?'), 'options' => [
                    'si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No'),
                ]],
                'testimonios' => ['type' => 'repeat', 'item_label' => $bt('Testimonio', 'Testimonial'), 'count' => 3, 'subfields' => [
                    'texto' => ['type' => 'textarea', 'label' => $bt('Testimonio', 'Testimonial')],
                    'nombre_cliente' => ['type' => 'text', 'label' => $bt('Nombre del cliente', 'Customer name')],
                ]],
                'seo_servicios' => ['type' => 'textarea', 'label' => $bt('¿Qué servicios desea promocionar principalmente?', 'Which services do you mainly want to promote?')],
                'seo_ciudades' => ['type' => 'textarea', 'label' => $bt('¿En qué ciudades quiere aparecer en Google?', 'In which cities do you want to appear on Google?')],
                'seo_palabras_clave' => ['type' => 'textarea', 'label' => $bt('¿Qué palabras utilizarían sus clientes para encontrar su negocio?', 'What words would your customers use to find your business?'), 'hint' => $bt('Ejemplo: "roofing company McAllen", "best plumber in Mission TX", "AC repair near me".', 'Example: "roofing company McAllen", "best plumber in Mission TX", "AC repair near me".')],
            ],
        ],

        'contacto' => [
            'title' => $bt('Datos de contacto', 'Contact information'),
            'desc' => $bt('La información y horarios que verán los visitantes del sitio.', 'The information and hours visitors will see on the site.'),
            'fields' => [
                'telefono_principal' => ['type' => 'tel', 'label' => $bt('Teléfono principal', 'Main phone')],
                'telefono_secundario' => ['type' => 'tel', 'label' => $bt('Teléfono secundario', 'Secondary phone')],
                'email_sitio' => ['type' => 'email', 'label' => $bt('Email de contacto', 'Contact email')],
                'whatsapp_sitio' => ['type' => 'tel', 'label' => $bt('WhatsApp', 'WhatsApp')],
                'direccion_sitio' => ['type' => 'text', 'label' => $bt('Dirección', 'Address')],
                'horarios' => ['type' => 'schedule', 'label' => $bt('Horarios de atención', 'Business hours'), 'days' => [
                    'lunes' => $bt('Lunes', 'Monday'), 'martes' => $bt('Martes', 'Tuesday'), 'miercoles' => $bt('Miércoles', 'Wednesday'),
                    'jueves' => $bt('Jueves', 'Thursday'), 'viernes' => $bt('Viernes', 'Friday'), 'sabado' => $bt('Sábado', 'Saturday'), 'domingo' => $bt('Domingo', 'Sunday'),
                ]],
                'campos_formulario' => ['type' => 'checkboxes', 'label' => $bt('¿Qué información desea solicitar en el formulario de contacto del sitio?', 'What information do you want to request in the site\'s contact form?'), 'other' => true, 'options' => [
                    'nombre' => $bt('Nombre', 'Name'),
                    'telefono' => $bt('Teléfono', 'Phone'),
                    'email' => $bt('Email', 'Email'),
                    'servicio_interes' => $bt('Servicio de interés', 'Service of interest'),
                    'direccion' => $bt('Dirección', 'Address'),
                    'fecha_preferida' => $bt('Fecha preferida', 'Preferred date'),
                    'presupuesto' => $bt('Presupuesto', 'Budget'),
                    'mensaje' => $bt('Mensaje', 'Message'),
                ]],
            ],
        ],

        'idioma_dominio' => [
            'title' => $bt('Idioma, dominio y hosting', 'Language, domain, and hosting'),
            'desc' => $bt('Datos técnicos para poner en línea tu sitio.', 'Technical details to get your site online.'),
            'fields' => [
                'idioma_sitio' => ['type' => 'radio', 'label' => $bt('¿Qué idioma desea utilizar en el sitio?', 'Which language do you want the site to use?'), 'options' => [
                    'en' => $bt('English', 'English'), 'es' => $bt('Español', 'Spanish'), 'bilingue' => $bt('English + Español', 'English + Spanish'),
                ]],
                'idioma_principal' => ['type' => 'radio', 'label' => $bt('Idioma principal (si el sitio será bilingüe)', 'Primary language (if the site will be bilingual)'), 'options' => [
                    'en' => $bt('English', 'English'), 'es' => $bt('Español', 'Spanish'),
                ]],
                'dominio_tiene' => ['type' => 'radio', 'label' => $bt('¿Ya tiene un dominio?', 'Do you already have a domain?'), 'options' => [
                    'si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No'),
                ]],
                'dominio_actual' => ['type' => 'text', 'label' => $bt('Dominio actual', 'Current domain'), 'hint' => $bt('Si respondió "Sí" arriba.', 'If you answered "Yes" above.')],
                'dominio_opciones' => ['type' => 'textarea', 'label' => $bt('Si NO tiene dominio, indique algunas opciones de nombre que le gustaría', 'If you do NOT have a domain, list some name options you\'d like'), 'hint' => $bt('Un nombre por línea. Ejemplo: companyname.com, companyname.net, companynameusa.com', 'One name per line. Example: companyname.com, companyname.net, companynameusa.com')],
                'hosting_tiene' => ['type' => 'radio', 'label' => $bt('¿Actualmente tiene hosting?', 'Do you currently have hosting?'), 'options' => [
                    'si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No'), 'no_seguro' => $bt('No estoy seguro', 'Not sure'),
                ]],
                'hosting_proveedor' => ['type' => 'text', 'label' => $bt('Proveedor actual', 'Current provider')],
                'hosting_usuario' => ['type' => 'text', 'label' => $bt('Usuario / acceso (si aplica)', 'Username / access (if applicable)'), 'hint' => $bt('No envíe contraseñas por este formulario. Los accesos se solicitarán de manera segura cuando sean necesarios.', 'Do not send passwords through this form. Access credentials will be requested securely when needed.')],
                'correos_desea' => ['type' => 'radio', 'label' => $bt('¿Desea correos electrónicos con su dominio?', 'Do you want email addresses with your domain?'), 'hint' => $bt('Ejemplo: info@companyname.com, sales@companyname.com, contact@companyname.com', 'Example: info@companyname.com, sales@companyname.com, contact@companyname.com'), 'options' => [
                    'si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No'),
                ]],
                'correos_lista' => ['type' => 'textarea', 'label' => $bt('Correos que desea crear', 'Email addresses you want to create'), 'hint' => $bt('Uno por línea.', 'One per line.')],
            ],
        ],

        'diseno_competencia' => [
            'title' => $bt('Diseño y competencia', 'Design and competitors'),
            'desc' => $bt('El estilo visual que buscas y a quién compites.', 'The visual style you\'re after and who you compete with.'),
            'fields' => [
                'diseno_preferencia' => ['type' => 'textarea', 'label' => $bt('¿Tiene alguna preferencia de diseño?', 'Do you have a design preference?'), 'hint' => $bt('Ejemplos: moderno, elegante, minimalista, corporativo, profesional, industrial, premium, tradicional, colorido.', 'Examples: modern, elegant, minimalist, corporate, professional, industrial, premium, traditional, colorful.')],
                'color_principal' => ['type' => 'text', 'label' => $bt('Color principal', 'Primary color')],
                'color_secundario' => ['type' => 'text', 'label' => $bt('Color secundario', 'Secondary color')],
                'color_otros' => ['type' => 'text', 'label' => $bt('Otros colores', 'Other colors')],
                'referencias' => ['type' => 'list', 'label' => $bt('Sitios web de referencia', 'Reference websites'), 'item_label' => $bt('Referencia', 'Reference'), 'count' => 5],
                'diseno_que_gusta' => ['type' => 'textarea', 'label' => $bt('¿Qué le gusta de estos sitios?', 'What do you like about these sites?')],
                'competidores' => ['type' => 'repeat', 'item_label' => $bt('Competidor', 'Competitor'), 'count' => 3, 'subfields' => [
                    'nombre' => ['type' => 'text', 'label' => $bt('Nombre', 'Name')],
                    'website' => ['type' => 'text', 'label' => $bt('Website', 'Website')],
                ]],
                'competencia_opinion' => ['type' => 'textarea', 'label' => $bt('¿Qué le gusta o no le gusta de las páginas de sus competidores?', 'What do you like or dislike about your competitors\' websites?')],
            ],
        ],

        'funcionalidades_legal' => [
            'title' => $bt('Funcionalidades y legal', 'Features and legal'),
            'desc' => $bt('Extras que quieras en el sitio y documentos legales.', 'Extras you want on the site and legal documents.'),
            'fields' => [
                'funcionalidades' => ['type' => 'checkboxes', 'label' => $bt('¿Desea alguna de las siguientes funcionalidades?', 'Do you want any of the following features?'), 'other' => true, 'options' => [
                    'citas' => $bt('Reservación de citas', 'Appointment booking'),
                    'calendario' => $bt('Calendario', 'Calendar'),
                    'cotizacion_online' => $bt('Cotización en línea', 'Online quote request'),
                    'chat' => $bt('Chat', 'Chat'),
                    'whatsapp' => $bt('WhatsApp', 'WhatsApp'),
                    'newsletter' => $bt('Newsletter', 'Newsletter'),
                    'blog' => $bt('Blog', 'Blog'),
                    'galeria' => $bt('Galería', 'Gallery'),
                    'reviews' => $bt('Reviews', 'Reviews'),
                    'ecommerce' => $bt('E-commerce', 'E-commerce'),
                    'pagos_online' => $bt('Pagos en línea', 'Online payments'),
                    'membresias' => $bt('Membresías', 'Memberships'),
                    'area_privada' => $bt('Área privada para clientes', 'Private client area'),
                    'crm' => $bt('Integración con CRM', 'CRM integration'),
                    'redes_sociales' => $bt('Integración con redes sociales', 'Social media integration'),
                ]],
                'info_legal' => ['type' => 'checkboxes', 'label' => $bt('¿Cuenta con alguno de estos documentos?', 'Do you have any of these documents?'), 'options' => [
                    'privacy_policy' => $bt('Privacy Policy', 'Privacy Policy'),
                    'terms' => $bt('Terms & Conditions', 'Terms & Conditions'),
                    'cookie_policy' => $bt('Cookie Policy', 'Cookie Policy'),
                    'disclaimer' => $bt('Disclaimer', 'Disclaimer'),
                    'ninguno' => $bt('No cuenta con estos documentos', 'Does not have these documents'),
                ]],
                'legal_link' => ['type' => 'text', 'label' => $bt('Si ya cuenta con ellos, comparta un link a los documentos', 'If you already have them, share a link to the documents')],
            ],
        ],

        'adicional_pendiente' => [
            'title' => $bt('Información adicional', 'Additional information'),
            'desc' => $bt('Cualquier otro detalle y qué material te falta por enviar.', 'Anything else, and what material you still need to send.'),
            'fields' => [
                'info_adicional' => ['type' => 'textarea', 'label' => $bt('¿Hay algo más que le gustaría incluir en su página?', 'Is there anything else you\'d like to include on your site?')],
                'material_pendiente' => ['type' => 'checkboxes', 'label' => $bt('Marque lo que todavía necesita proporcionar', 'Mark what you still need to provide'), 'other' => true, 'options' => [
                    'logo' => $bt('Logo', 'Logo'),
                    'fotos' => $bt('Fotografías', 'Photos'),
                    'textos' => $bt('Textos', 'Copy/text'),
                    'info_servicios' => $bt('Información de servicios', 'Service information'),
                    'info_contacto' => $bt('Información de contacto', 'Contact information'),
                    'redes_sociales' => $bt('Redes sociales', 'Social media'),
                    'google_business' => $bt('Google Business Profile', 'Google Business Profile'),
                    'testimonios' => $bt('Testimonios', 'Testimonials'),
                    'info_legal' => $bt('Información legal', 'Legal information'),
                    'dominio' => $bt('Dominio', 'Domain'),
                ]],
            ],
        ],

        'resumen' => [
            'title' => $bt('Revisión final', 'Final review'),
            'desc' => $bt('Antes de comenzar el diseño, confirmaremos con usted: estructura del sitio, páginas incluidas, servicios/productos, información principal, estilo visual, colores, material disponible, funcionalidades y fecha estimada de entrega.', 'Before starting the design, we will confirm with you: site structure, included pages, services/products, main information, visual style, colors, available material, features, and estimated delivery date.'),
            'fields' => [
                'confirmacion' => ['type' => 'checkboxes', 'label' => $bt('Confirmación', 'Confirmation'), 'required' => true, 'options' => [
                    'confirmado' => $bt('Confirmo que la información proporcionada es correcta hasta donde tengo disponible.', 'I confirm that the information provided is correct to the best of my knowledge.'),
                ]],
            ],
        ],
    ];
}

function ninubo_brief_schema(string $lang = 'es'): array {
    static $cache = [];
    $lang = $lang === 'en' ? 'en' : 'es';
    if (isset($cache[$lang])) {
        return $cache[$lang];
    }

    $resolved = [];
    foreach (ninubo_brief_schema_raw() as $stepKey => $step) {
        $resolvedStep = $step;
        $resolvedStep['title'] = ninubo_brief_resolve_text($step['title'], $lang);
        if (isset($step['desc'])) {
            $resolvedStep['desc'] = ninubo_brief_resolve_text($step['desc'], $lang);
        }
        $resolvedFields = [];
        foreach ($step['fields'] as $fieldName => $field) {
            $resolvedFields[$fieldName] = ninubo_brief_resolve_field($field, $lang);
        }
        $resolvedStep['fields'] = $resolvedFields;
        $resolved[$stepKey] = $resolvedStep;
    }

    $cache[$lang] = $resolved;
    return $resolved;
}
