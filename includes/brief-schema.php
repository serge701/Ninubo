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
            'except' => ['landing'],
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
            'title' => $bt('Estructura del sitio', 'Site structure'),
            'desc' => $bt('Indique qué secciones o páginas desea incluir.', 'Indicate which sections or pages you want to include.'),
            'fields' => [
                // Starter: una sola página con hasta 5 secciones.
                'secciones_landing' => ['only' => ['landing'], 'type' => 'checkboxes', 'label' => $bt('Secciones de su página', 'Sections of your page'), 'hint' => $bt('Su paquete Starter es una página con hasta 5 secciones. Elija las 5 más importantes; secciones adicionales se pueden cotizar.', 'Your Starter package is one page with up to 5 sections. Choose the 5 most important; extra sections can be quoted.'), 'other' => true, 'options' => [
                    'hero' => $bt('Portada con mensaje principal', 'Hero with main message'),
                    'about' => $bt('Sobre nosotros', 'About us'),
                    'services' => $bt('Servicios', 'Services'),
                    'gallery' => $bt('Galería / trabajos realizados', 'Gallery / past work'),
                    'reviews' => $bt('Reseñas / testimonios', 'Reviews / testimonials'),
                    'faq' => $bt('Preguntas frecuentes', 'FAQ'),
                    'service_area' => $bt('Área de servicio / mapa', 'Service area / map'),
                    'contact' => $bt('Contacto con formulario', 'Contact with form'),
                ]],
                'paginas_sitio' => ['except' => ['landing'], 'type' => 'checkboxes', 'label' => $bt('Páginas a incluir', 'Pages to include'),
                    'hint_by_profile' => [
                        'business' => $bt('Su paquete Business incluye hasta 7 páginas. Páginas adicionales: US$100 c/u.', 'Your Business package includes up to 7 pages. Additional pages: US$100 each.'),
                        'premium' => $bt('Su paquete Premium incluye hasta 12 páginas. Páginas adicionales: US$100 c/u.', 'Your Premium package includes up to 12 pages. Additional pages: US$100 each.'),
                    ],
                    'other' => true, 'options' => [
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
                'historia_empresa' => ['except' => ['landing'], 'type' => 'textarea', 'label' => $bt('Historia de la empresa', 'Company history'), 'hint' => $bt('Cuéntenos brevemente cómo inició el negocio.', 'Briefly tell us how the business started.')],
                'anos_experiencia' => ['type' => 'text', 'label' => $bt('¿Cuántos años de experiencia tiene?', 'How many years of experience do you have?')],
                'mision_filosofia' => ['except' => ['landing'], 'type' => 'textarea', 'label' => $bt('Misión / filosofía', 'Mission / philosophy'), 'hint' => $bt('¿Qué representa su empresa?', 'What does your company stand for?')],
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
                'idioma_sitio' => ['type' => 'radio', 'label' => $bt('¿Qué idioma desea utilizar en el sitio?', 'Which language do you want the site to use?'),
                    'hint_by_profile' => [
                        'landing' => $bt('El paquete Starter es en un idioma. La versión bilingüe se puede agregar (US$200–400).', 'The Starter package is in one language. A bilingual version can be added (US$200–400).'),
                        'business' => $bt('Su paquete incluye versión bilingüe Inglés/Español.', 'Your package includes a bilingual English/Spanish version.'),
                        'premium' => $bt('Su paquete incluye versión bilingüe Inglés/Español.', 'Your package includes a bilingual English/Spanish version.'),
                    ],
                    'options' => [
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
                'competidores' => ['except' => ['landing'], 'type' => 'repeat', 'item_label' => $bt('Competidor', 'Competitor'), 'count' => 3, 'subfields' => [
                    'nombre' => ['type' => 'text', 'label' => $bt('Nombre', 'Name')],
                    'website' => ['type' => 'text', 'label' => $bt('Website', 'Website')],
                ]],
                'competencia_opinion' => ['except' => ['landing'], 'type' => 'textarea', 'label' => $bt('¿Qué le gusta o no le gusta de las páginas de sus competidores?', 'What do you like or dislike about your competitors\' websites?')],
            ],
        ],

        'tienda' => [
            'only' => ['ecommerce'],
            'title' => $bt('Tienda en línea', 'Online store'),
            'desc' => $bt('Lo que necesitamos saber para armar su tienda.', 'What we need to know to build your store.'),
            'fields' => [
                'tienda_num_productos' => ['type' => 'radio', 'label' => $bt('¿Cuántos productos venderá aproximadamente?', 'About how many products will you sell?'), 'options' => [
                    'hasta_20' => $bt('Hasta 20', 'Up to 20'),
                    '20_100' => $bt('20 a 100', '20 to 100'),
                    '100_500' => $bt('100 a 500', '100 to 500'),
                    'mas_500' => $bt('Más de 500', 'More than 500'),
                ]],
                'tienda_categorias' => ['type' => 'textarea', 'label' => $bt('Categorías de productos', 'Product categories'), 'hint' => $bt('Ejemplo: Ropa de hombre, Ropa de mujer, Accesorios.', 'Example: Men\'s clothing, Women\'s clothing, Accessories.')],
                'tienda_variantes' => ['type' => 'radio', 'label' => $bt('¿Sus productos tienen variantes (talla, color, tamaño)?', 'Do your products have variants (size, color, etc.)?'), 'options' => [
                    'si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No'),
                ]],
                'tienda_info_productos' => ['type' => 'radio', 'label' => $bt('¿Tiene lista la información de sus productos (fotos, descripciones, precios)?', 'Is your product information ready (photos, descriptions, prices)?'), 'options' => [
                    'completa' => $bt('Sí, completa', 'Yes, complete'),
                    'parcial' => $bt('Parcialmente', 'Partially'),
                    'no' => $bt('No, necesito ayuda', 'No, I need help'),
                ]],
                'tienda_pagos' => ['type' => 'checkboxes', 'label' => $bt('¿Qué formas de pago desea aceptar?', 'Which payment methods do you want to accept?'), 'other' => true, 'options' => [
                    'tarjeta' => $bt('Tarjeta de crédito / débito', 'Credit / debit card'),
                    'paypal' => $bt('PayPal', 'PayPal'),
                    'apple_google_pay' => $bt('Apple Pay / Google Pay', 'Apple Pay / Google Pay'),
                    'zelle' => $bt('Zelle', 'Zelle'),
                    'efectivo_entrega' => $bt('Efectivo al recoger / entregar', 'Cash on pickup / delivery'),
                ]],
                'tienda_procesador' => ['type' => 'text', 'label' => $bt('¿Ya tiene cuenta con algún procesador de pagos?', 'Do you already have a payment processor account?'), 'hint' => $bt('Ejemplo: Stripe, Square, PayPal, Clover.', 'Example: Stripe, Square, PayPal, Clover.')],
                'tienda_entrega' => ['type' => 'checkboxes', 'label' => $bt('¿Cómo entregará sus productos?', 'How will you deliver your products?'), 'other' => true, 'options' => [
                    'envio_usa' => $bt('Envío a todo EE. UU.', 'Shipping anywhere in the US'),
                    'entrega_local' => $bt('Entrega local', 'Local delivery'),
                    'recoger' => $bt('Recoger en tienda', 'In-store pickup'),
                    'digital' => $bt('Productos digitales / descargables', 'Digital / downloadable products'),
                ]],
                'tienda_envio_costos' => ['type' => 'textarea', 'label' => $bt('¿Cómo cobra el envío?', 'How do you charge for shipping?'), 'hint' => $bt('Ejemplo: tarifa fija de $10, gratis en compras mayores a $75, según peso.', 'Example: flat $10 rate, free over $75, by weight.')],
                'tienda_inventario' => ['type' => 'radio', 'label' => $bt('¿Necesita control de inventario en la tienda?', 'Do you need inventory tracking in the store?'), 'options' => [
                    'si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No'),
                ]],
                'tienda_politicas' => ['type' => 'textarea', 'label' => $bt('Políticas de devolución y cambios', 'Return and exchange policy'), 'hint' => $bt('Si ya las tiene, péguelas aquí o indique dónde encontrarlas.', 'If you already have one, paste it here or tell us where to find it.')],
            ],
        ],

        'premium' => [
            'only' => ['premium'],
            'title' => $bt('Funciones Premium', 'Premium features'),
            'desc' => $bt('Blog, formularios avanzados, integraciones, SEO local y animaciones incluidos en su paquete.', 'Blog, advanced forms, integrations, local SEO, and animations included in your package.'),
            'fields' => [
                'blog_quien_escribe' => ['type' => 'radio', 'label' => $bt('Blog / noticias: ¿quién escribirá los artículos?', 'Blog / news: who will write the articles?'), 'options' => [
                    'cliente' => $bt('Nosotros (el negocio)', 'We will (the business)'),
                    'ninubo' => $bt('Quisiera cotizar redacción profesional', 'I\'d like a quote for professional writing'),
                    'mixto' => $bt('Una combinación', 'A combination'),
                ]],
                'blog_temas' => ['type' => 'textarea', 'label' => $bt('Temas sobre los que le gustaría publicar', 'Topics you would like to publish about'), 'hint' => $bt('Ejemplo: consejos de mantenimiento de techos, proyectos recientes, temporada de huracanes.', 'Example: roof maintenance tips, recent projects, hurricane season.')],
                'formularios_avanzados' => ['type' => 'checkboxes', 'label' => $bt('¿Qué formularios avanzados necesita?', 'Which advanced forms do you need?'), 'other' => true, 'options' => [
                    'cotizacion' => $bt('Solicitud de cotización por pasos', 'Multi-step quote request'),
                    'citas' => $bt('Solicitud de cita', 'Appointment request'),
                    'archivos' => $bt('Envío de fotos o archivos', 'Photo / file upload'),
                    'empleo' => $bt('Solicitud de empleo', 'Job application'),
                    'encuesta' => $bt('Encuesta / evaluación', 'Survey / assessment'),
                ]],
                'integraciones' => ['type' => 'checkboxes', 'label' => $bt('¿Con qué herramientas debe conectarse el sitio?', 'Which tools should the site connect to?'), 'other' => true, 'hint' => $bt('Incluye integraciones externas básicas; las más complejas se cotizan aparte.', 'Basic external integrations are included; complex ones are quoted separately.'), 'options' => [
                    'agenda' => $bt('Agenda en línea (Calendly, Square, Google Calendar)', 'Online booking (Calendly, Square, Google Calendar)'),
                    'crm' => $bt('CRM', 'CRM'),
                    'email_marketing' => $bt('Email marketing (Mailchimp, etc.)', 'Email marketing (Mailchimp, etc.)'),
                    'pagos' => $bt('Pagos / depósitos en línea', 'Online payments / deposits'),
                    'chat' => $bt('Chat en vivo', 'Live chat'),
                    'resenas_widget' => $bt('Widget de reseñas de Google', 'Google reviews widget'),
                ]],
                'herramientas_actuales' => ['type' => 'textarea', 'label' => $bt('¿Qué sistemas usa hoy su negocio?', 'What systems does your business use today?'), 'hint' => $bt('Nombre del programa y para qué lo usa. Nos ayuda a planear las integraciones.', 'Program name and what you use it for. It helps us plan the integrations.')],
                'seo_local_ciudades' => ['type' => 'textarea', 'label' => $bt('SEO local: ciudades prioritarias, en orden de importancia', 'Local SEO: priority cities, in order of importance'), 'hint' => $bt('Ejemplo: McAllen, Edinburg, Mission, Pharr, Weslaco, Harlingen, Brownsville.', 'Example: McAllen, Edinburg, Mission, Pharr, Weslaco, Harlingen, Brownsville.')],
                'gbp_acceso' => ['type' => 'radio', 'label' => $bt('¿Puede darnos acceso a su Google Business Profile?', 'Can you give us access to your Google Business Profile?'), 'options' => [
                    'si' => $bt('Sí', 'Yes'),
                    'no_tengo' => $bt('No tengo perfil, necesito crearlo', 'I don\'t have one, I need to create it'),
                    'no_se' => $bt('No sé cómo / necesito ayuda', 'I don\'t know how / need help'),
                ]],
                'animaciones' => ['type' => 'radio', 'label' => $bt('¿Qué nivel de animaciones y efectos prefiere?', 'What level of animations and effects do you prefer?'), 'options' => [
                    'sutil' => $bt('Sutil y profesional', 'Subtle and professional'),
                    'moderado' => $bt('Moderado, que llame la atención', 'Moderate, eye-catching'),
                    'llamativo' => $bt('Muy dinámico', 'Very dynamic'),
                ]],
            ],
        ],

        'funcionalidades_legal' => [
            'except' => ['landing'],
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

/**
 * Perfiles de brief: cada paquete del catálogo del CRM (tabla packages.brief_profile)
 * elige uno, y el cliente solo ve las preguntas de ese perfil.
 *  - Un paso o campo con 'only' => [...] aparece solo para esos perfiles.
 *  - Un paso o campo con 'except' => [...] se oculta para esos perfiles.
 *  - Un campo con 'hint_by_profile' => [perfil => texto] cambia su ayuda según el paquete.
 *  - Sin perfil (briefs anteriores a los perfiles) = todo lo que no tiene 'only' (igual que 'business').
 */
function ninubo_brief_profiles(): array {
    return [
        'landing' => 'Starter — landing page',
        'business' => 'Business website',
        'premium' => 'Premium website',
        'ecommerce' => 'Online store',
    ];
}

function ninubo_brief_applies(array $item, ?string $profile): bool {
    if (isset($item['only']) && !in_array($profile, $item['only'], true)) {
        return false;
    }
    if (isset($item['except']) && $profile !== null && in_array($profile, $item['except'], true)) {
        return false;
    }
    return true;
}

function ninubo_brief_schema(string $lang = 'es', ?string $profile = null): array {
    static $cache = [];
    $lang = $lang === 'en' ? 'en' : 'es';
    $profile = ($profile !== null && isset(ninubo_brief_profiles()[$profile])) ? $profile : null;
    $cacheKey = $lang . '|' . ($profile ?? '');
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $resolved = [];
    foreach (ninubo_brief_schema_raw() as $stepKey => $step) {
        if (!ninubo_brief_applies($step, $profile)) {
            continue;
        }
        $step['fields'] = array_filter($step['fields'], fn($field) => ninubo_brief_applies($field, $profile));
        // 'hint_by_profile' => [perfil => texto] reemplaza el hint según el paquete del cliente.
        foreach ($step['fields'] as $fieldName => $field) {
            if (isset($field['hint_by_profile'])) {
                if ($profile !== null && isset($field['hint_by_profile'][$profile])) {
                    $field['hint'] = $field['hint_by_profile'][$profile];
                }
                unset($field['hint_by_profile']);
                $step['fields'][$fieldName] = $field;
            }
        }
        unset($step['only'], $step['except']);
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

    $cache[$cacheKey] = $resolved;
    return $resolved;
}
