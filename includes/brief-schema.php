<?php
/**
 * Website brief questions — the ONLY place where they are defined. Used by the public form (/brief.php),
 * by Crm\Core\BriefForm (draws, collects and displays answers) and by the CRM's brief page.
 *
 * Bilingual: every visible text is ninubo_brief_bt('Español', 'English'); ninubo_brief_schema($lang, $profile)
 * resolves it. Checkbox/radio answers are stored as stable keys (e.g. 'nuevos_clientes'), never the visible text,
 * so an answer means the same whatever language the client used.
 *
 * Field types:
 *   text, tel, email, textarea  -> string
 *   checkboxes                  -> array of option keys (+ free text when 'other' => true)
 *   radio                       -> one option key (or free text when 'other' => true)
 *   repeat                      -> numbered sub-forms (e.g. 3 services)
 *   list                        -> numbered single inputs (e.g. 5 reference sites)
 *   schedule                    -> one text input per weekday
 *
 * Per-package profiles (see ninubo_brief_profiles()):
 *   'only' => [...] / 'except' => [...]   on a step or field: show it only for / hide it for those profiles
 *   'hint_by_profile' => [profile => bt]  replaces the field's hint for that profile
 *   'options_only' => [option => [...]]   option shown in the form only for those profiles ([] = never;
 *                                         kept so answers saved by older versions still show their label)
 *   'prefill' => 'client column'          first time the client opens the brief, the field starts with that
 *                                         value from the CRM client record (they confirm or correct it)
 *
 * Answer keys must never be renamed: saved briefs keep them. A question that is removed simply stops being
 * asked; the CRM still lists its old answers under "Answers from an earlier version of the form".
 */

/** Bilingual text. ninubo_brief_schema() resolves it to the requested language. */
function ninubo_brief_bt(string $es, string $en): array {
    return ['es' => $es, 'en' => $en];
}

function ninubo_brief_resolve_text($value, string $lang): string {
    if (is_array($value)) {
        return $value[$lang] ?? ($value['es'] ?? '');
    }
    return (string)$value;
}

function ninubo_brief_resolve_field(array $field, string $lang, ?string $profile): array {
    $out = $field;
    if (isset($field['hint_by_profile'])) {
        if ($profile !== null && isset($field['hint_by_profile'][$profile])) {
            $out['hint'] = $field['hint_by_profile'][$profile];
        }
        unset($out['hint_by_profile']);
    }
    foreach (['label', 'hint', 'item_label', 'placeholder'] as $key) {
        if (isset($out[$key])) {
            $out[$key] = ninubo_brief_resolve_text($out[$key], $lang);
        }
    }
    if (isset($field['options'])) {
        // option_labels: every option (to display old answers); options: the ones this profile is offered.
        $labels = [];
        $offered = [];
        foreach ($field['options'] as $key => $label) {
            $labels[$key] = ninubo_brief_resolve_text($label, $lang);
            $only = $field['options_only'][$key] ?? null;
            if ($only === null || ($profile !== null && in_array($profile, $only, true))) {
                $offered[$key] = $labels[$key];
            }
        }
        $out['option_labels'] = $labels;
        $out['options'] = $offered;
        unset($out['options_only']);
    }
    if (isset($field['days'])) {
        $out['days'] = array_map(fn($d) => ninubo_brief_resolve_text($d, $lang), $field['days']);
    }
    if (isset($field['subfields'])) {
        $out['subfields'] = array_map(fn($f) => ninubo_brief_resolve_field($f, $lang, $profile), $field['subfields']);
    }
    return $out;
}

function ninubo_brief_schema_raw(): array {
    $bt = 'ninubo_brief_bt';
    $yesNo = ['si' => $bt('Sí', 'Yes'), 'no' => $bt('No', 'No')];

    return [
        'negocio' => [
            'title' => $bt('Su negocio', 'Your business'),
            'desc' => $bt('Quién es usted, qué hace su negocio y a quién atiende. Ya llenamos lo que tenemos; corrija lo que haga falta.', 'Who you are, what your business does and who it serves. We filled in what we already have; correct anything as needed.'),
            'fields' => [
                'nombre_comercial' => ['type' => 'text', 'required' => true, 'prefill' => 'company_name', 'label' => $bt('Nombre comercial', 'Business name'), 'hint' => $bt('Como quiere que aparezca en la página.', 'As you want it to appear on the website.')],
                'nombre_legal' => ['type' => 'text', 'label' => $bt('Nombre legal de la empresa (si es diferente)', 'Legal company name (if different)')],
                'contacto_nombre' => ['type' => 'text', 'required' => true, 'prefill' => 'contact_name', 'label' => $bt('Persona de contacto para el proyecto', 'Contact person for the project')],
                'email' => ['type' => 'email', 'required' => true, 'prefill' => 'email', 'label' => $bt('Correo de la persona de contacto', 'Contact person\'s email')],
                'telefono' => ['type' => 'tel', 'prefill' => 'phone', 'label' => $bt('Teléfono de la persona de contacto', 'Contact person\'s phone')],
                'descripcion_negocio' => ['type' => 'textarea', 'label' => $bt('¿A qué se dedica su empresa?', 'What does your business do?'), 'hint' => $bt('Describa brevemente qué hace su negocio y qué productos o servicios ofrece.', 'Briefly describe what your business does and which products or services you offer.')],
                'cliente_ideal' => ['type' => 'textarea', 'label' => $bt('¿Quién es su cliente ideal?', 'Who is your ideal customer?'), 'hint' => $bt('El tipo de persona o empresa que normalmente contrata sus servicios.', 'The type of person or company that typically hires your services.')],
                'area_geografica' => ['type' => 'textarea', 'label' => $bt('¿En qué ciudades o áreas ofrece sus servicios?', 'Which cities or areas do you serve?'), 'hint' => $bt('En orden de importancia; son también las ciudades donde quiere aparecer en Google. Ejemplo: McAllen, Edinburg, Mission, Pharr, Weslaco, Brownsville o todo el Rio Grande Valley.', 'In order of importance; these are also the cities where you want to show up on Google. Example: McAllen, Edinburg, Mission, Pharr, Weslaco, Brownsville or the whole Rio Grande Valley.')],
                'objetivo_web' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('¿Cuál es el principal objetivo de su página web?', 'What is the main goal of your website?'), 'options' => [
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
                ], 'options_only' => ['vender_productos' => ['ecommerce'], 'reservar_citas' => ['premium']]],
            ],
        ],

        'servicios' => [
            'title' => $bt('Servicios / productos', 'Services / products'),
            'desc' => $bt('Los servicios o productos que desea mostrar en la página.', 'The services or products you want to show on the website.'),
            'fields' => [
                'servicios' => ['type' => 'repeat', 'item_label' => $bt('Servicio / producto', 'Service / product'), 'count' => 3, 'subfields' => [
                    'nombre' => ['type' => 'text', 'label' => $bt('Nombre', 'Name')],
                    'descripcion' => ['type' => 'textarea', 'label' => $bt('Descripción', 'Description')],
                    'precio' => ['type' => 'text', 'label' => $bt('Precio (si desea mostrarlo)', 'Price (if you want to show it)')],
                ]],
                'servicios_adicionales' => ['type' => 'textarea', 'label' => $bt('Otros servicios o productos', 'Other services or products'), 'hint' => $bt('Agregue todos los que sean necesarios, uno por línea.', 'Add as many as needed, one per line.')],
            ],
        ],

        'estructura_sitio' => [
            'title' => $bt('Estructura del sitio', 'Site structure'),
            'desc' => $bt('Qué secciones o páginas desea incluir.', 'Which sections or pages you want to include.'),
            'fields' => [
                // Starter: one page with up to 5 sections.
                'secciones_landing' => ['only' => ['landing'], 'type' => 'checkboxes', 'other' => true, 'label' => $bt('Secciones de su página', 'Sections of your page'), 'hint' => $bt('Su paquete Starter es una página con hasta 5 secciones. Elija las 5 más importantes; secciones adicionales se pueden cotizar.', 'Your Starter package is one page with up to 5 sections. Choose the 5 most important; extra sections can be quoted.'), 'options' => [
                    'hero' => $bt('Portada con mensaje principal', 'Hero with main message'),
                    'about' => $bt('Sobre nosotros', 'About us'),
                    'services' => $bt('Servicios', 'Services'),
                    'gallery' => $bt('Galería / trabajos realizados', 'Gallery / past work'),
                    'reviews' => $bt('Reseñas / testimonios', 'Reviews / testimonials'),
                    'faq' => $bt('Preguntas frecuentes', 'FAQ'),
                    'service_area' => $bt('Área de servicio / mapa', 'Service area / map'),
                    'contact' => $bt('Contacto con formulario', 'Contact with form'),
                ]],
                'paginas_sitio' => ['except' => ['landing'], 'type' => 'checkboxes', 'other' => true, 'label' => $bt('Páginas a incluir', 'Pages to include'),
                    'hint_by_profile' => [
                        'business' => $bt('Su paquete Business incluye hasta 7 páginas (páginas adicionales: US$100 c/u).', 'Your Business package includes up to 7 pages (additional pages: US$100 each).'),
                        'premium' => $bt('Su paquete Premium incluye hasta 12 páginas (páginas adicionales: US$100 c/u).', 'Your Premium package includes up to 12 pages (additional pages: US$100 each).'),
                    ],
                    'options' => [
                        'home' => $bt('Inicio', 'Home'),
                        'about' => $bt('Nosotros', 'About us'),
                        'services' => $bt('Servicios', 'Services'),
                        'service_pages' => $bt('Una página por servicio', 'A page for each service'),
                        'products' => $bt('Productos', 'Products'),
                        'gallery' => $bt('Galería', 'Gallery'),
                        'portfolio' => $bt('Portafolio / proyectos', 'Portfolio / projects'),
                        'reviews' => $bt('Testimonios', 'Reviews'),
                        'faq' => $bt('Preguntas frecuentes', 'FAQ'),
                        'blog' => $bt('Blog / noticias', 'Blog / news'),
                        'contact' => $bt('Contacto', 'Contact'),
                        'booking' => $bt('Reservaciones', 'Booking'),
                    ],
                    // Blog and online booking are Premium features.
                    'options_only' => ['blog' => ['premium'], 'booking' => ['premium']],
                ],
            ],
        ],

        'inicio_nosotros' => [
            'title' => $bt('Inicio y Nosotros', 'Home and About'),
            'desc' => $bt('El mensaje principal de su página y la historia de su empresa.', 'The main message of your website and your company\'s story.'),
            'fields' => [
                'titulo_principal' => ['type' => 'text', 'label' => $bt('¿Qué mensaje desea que vea primero el visitante?', 'What message do you want visitors to see first?'), 'hint' => $bt('Ejemplo: "Professional Roofing Services in the Rio Grande Valley". Si no tiene una frase definida, podemos proponer opciones.', 'Example: "Professional Roofing Services in the Rio Grande Valley". If you don\'t have one yet, we can propose options.')],
                'subtitulo' => ['type' => 'textarea', 'label' => $bt('Subtítulo', 'Subtitle'), 'hint' => $bt('Explique brevemente qué ofrece y por qué deberían contratarlo.', 'Briefly explain what you offer and why visitors should hire you.')],
                'cta_principal' => ['type' => 'radio', 'other' => true, 'label' => $bt('¿Qué quiere que haga el visitante? (botón principal)', 'What do you want the visitor to do? (main button)'), 'options' => [
                    'call_now' => $bt('Llamar (Call Now)', 'Call Now'),
                    'free_quote' => $bt('Pedir cotización (Get a Free Quote)', 'Get a Free Quote'),
                    'contact_us' => $bt('Contactarnos (Contact Us)', 'Contact Us'),
                    'schedule_appt' => $bt('Agendar una cita (Schedule an Appointment)', 'Schedule an Appointment'),
                    'send_message' => $bt('Enviar un mensaje (Send a Message)', 'Send a Message'),
                    'view_services' => $bt('Ver servicios (View Our Services)', 'View Our Services'),
                ]],
                'anos_experiencia' => ['type' => 'text', 'label' => $bt('¿Cuántos años de experiencia tiene?', 'How many years of experience do you have?')],
                'historia_empresa' => ['except' => ['landing'], 'type' => 'textarea', 'label' => $bt('Historia de la empresa', 'Company history'), 'hint' => $bt('Cuéntenos brevemente cómo inició el negocio.', 'Briefly tell us how the business started.')],
                'mision_filosofia' => ['except' => ['landing'], 'type' => 'textarea', 'label' => $bt('Misión / filosofía', 'Mission / philosophy'), 'hint' => $bt('¿Qué representa su empresa?', 'What does your company stand for?')],
                'diferenciador' => ['type' => 'textarea', 'label' => $bt('¿Qué hace diferente a su negocio de la competencia?', 'What sets your business apart from the competition?'), 'hint' => $bt('Por ejemplo: experiencia, precio, calidad, rapidez, garantía, atención personalizada, servicio bilingüe, servicio 24/7.', 'For example: experience, price, quality, speed, warranty, personal attention, bilingual service, 24/7 service.')],
            ],
        ],

        'visual_redes' => [
            'title' => $bt('Material visual y redes', 'Visual assets and social media'),
            'desc' => $bt('Su logo, fotos y redes sociales.', 'Your logo, photos and social media.'),
            'fields' => [
                'material_visual' => ['type' => 'checkboxes', 'label' => $bt('¿Qué materiales tiene disponibles?', 'Which materials do you have available?'), 'options' => [
                    'logo' => $bt('Logotipo', 'Logo'),
                    'fotos_negocio' => $bt('Fotografías del negocio', 'Photos of the business'),
                    'fotos_trabajos' => $bt('Fotografías de trabajos realizados', 'Photos of completed work'),
                    'fotos_productos' => $bt('Fotografías de productos', 'Product photos'),
                    'fotos_equipo' => $bt('Fotografías del equipo', 'Photos of the team'),
                    'videos' => $bt('Videos', 'Videos'),
                    'material_publicitario' => $bt('Material publicitario', 'Advertising materials'),
                    'brand_guidelines' => $bt('Manual de identidad (brand guidelines)', 'Brand guidelines'),
                ]],
                'archivos_link' => ['type' => 'text', 'label' => $bt('Link a sus archivos', 'Link to your files'), 'hint' => $bt('Suba su logo, fotos y videos (en la mejor calidad posible) a una carpeta de Google Drive, Dropbox o WeTransfer y pegue aquí el link.', 'Upload your logo, photos and videos (in the best quality available) to a Google Drive, Dropbox or WeTransfer folder and paste the link here.'), 'placeholder' => $bt('https://drive.google.com/…', 'https://drive.google.com/…')],
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
            'desc' => $bt('Su presencia en Google, testimonios y cómo lo buscan sus clientes.', 'Your Google presence, testimonials and how customers search for you.'),
            'fields' => [
                'google_business' => ['type' => 'radio', 'label' => $bt('¿Su negocio ya aparece en Google Maps?', 'Does your business already appear on Google Maps?'), 'options' => $yesNo + ['no_seguro' => $bt('No estoy seguro', 'Not sure')]],
                'google_maps_link' => ['type' => 'text', 'label' => $bt('Link de Google Maps / Google Business Profile', 'Google Maps / Google Business Profile link')],
                'mostrar_resenas' => ['type' => 'radio', 'label' => $bt('¿Desea mostrar reseñas de clientes en la página?', 'Do you want to show customer reviews on the website?'), 'options' => $yesNo],
                'testimonios' => ['type' => 'repeat', 'item_label' => $bt('Testimonio', 'Testimonial'), 'count' => 3, 'subfields' => [
                    'texto' => ['type' => 'textarea', 'label' => $bt('Testimonio', 'Testimonial')],
                    'nombre_cliente' => ['type' => 'text', 'label' => $bt('Nombre del cliente', 'Customer name')],
                ]],
                'seo_servicios' => ['type' => 'textarea', 'label' => $bt('¿Qué servicios desea promocionar principalmente?', 'Which services do you mainly want to promote?')],
                'seo_palabras_clave' => ['type' => 'textarea', 'label' => $bt('¿Qué palabras usarían sus clientes para encontrarlo en Google?', 'What words would your customers use to find you on Google?'), 'hint' => $bt('Ejemplo: "roofing company McAllen", "plomero en Mission TX", "AC repair near me".', 'Example: "roofing company McAllen", "best plumber in Mission TX", "AC repair near me".')],
            ],
        ],

        'contacto' => [
            'title' => $bt('Contacto en el sitio web', 'Contact details on the website'),
            'desc' => $bt('Lo que verán los visitantes de su página. Ya llenamos lo que tenemos; corrija o complete lo que haga falta.', 'What visitors will see on your website. We filled in what we already have; correct or complete it as needed.'),
            'fields' => [
                'telefono_principal' => ['type' => 'tel', 'prefill' => 'phone', 'label' => $bt('Teléfono principal', 'Main phone')],
                'telefono_secundario' => ['type' => 'tel', 'prefill' => 'alt_phone', 'label' => $bt('Teléfono secundario', 'Secondary phone')],
                'whatsapp_sitio' => ['type' => 'tel', 'label' => $bt('WhatsApp', 'WhatsApp')],
                'email_sitio' => ['type' => 'email', 'prefill' => 'email', 'label' => $bt('Correo de contacto', 'Contact email')],
                'direccion_sitio' => ['type' => 'text', 'prefill' => 'address', 'label' => $bt('Dirección', 'Address'), 'hint' => $bt('Déjela en blanco si no quiere mostrar una dirección (por ejemplo, si trabaja a domicilio).', 'Leave it blank if you don\'t want to show an address (for example, if you work on-site at customers\' homes).')],
                'horarios' => ['type' => 'schedule', 'label' => $bt('Horario de atención', 'Business hours'), 'days' => [
                    'lunes' => $bt('Lunes', 'Monday'), 'martes' => $bt('Martes', 'Tuesday'), 'miercoles' => $bt('Miércoles', 'Wednesday'),
                    'jueves' => $bt('Jueves', 'Thursday'), 'viernes' => $bt('Viernes', 'Friday'), 'sabado' => $bt('Sábado', 'Saturday'), 'domingo' => $bt('Domingo', 'Sunday'),
                ], 'placeholder' => $bt('Ej. 9:00 am – 6:00 pm o Cerrado', 'E.g. 9:00 am – 6:00 pm or Closed')],
                'campos_formulario' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('¿Qué datos desea pedir en el formulario de contacto del sitio?', 'What information should the website\'s contact form ask for?'), 'options' => [
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
            'title' => $bt('Idioma y dominio', 'Language and domain'),
            'desc' => $bt('Datos para poner su sitio en línea. Su paquete incluye el dominio y el hosting del primer año.', 'Details to get your site online. Your package includes the domain and hosting for the first year.'),
            'fields' => [
                'idioma_sitio' => ['type' => 'radio', 'label' => $bt('¿En qué idioma será el sitio?', 'Which language will the website use?'),
                    'hint_by_profile' => [
                        'landing' => $bt('El paquete Starter es en un idioma. La versión bilingüe se puede agregar (US$200–400).', 'The Starter package is in one language. A bilingual version can be added (US$200–400).'),
                        'business' => $bt('Su paquete incluye versión bilingüe inglés/español.', 'Your package includes a bilingual English/Spanish version.'),
                        'premium' => $bt('Su paquete incluye versión bilingüe inglés/español.', 'Your package includes a bilingual English/Spanish version.'),
                    ],
                    'options' => ['en' => $bt('Inglés', 'English'), 'es' => $bt('Español', 'Spanish'), 'bilingue' => $bt('Inglés + español', 'English + Spanish')]],
                'idioma_principal' => ['type' => 'radio', 'label' => $bt('Idioma principal (si el sitio será bilingüe)', 'Main language (if the website will be bilingual)'), 'options' => ['en' => $bt('Inglés', 'English'), 'es' => $bt('Español', 'Spanish')]],
                'dominio_tiene' => ['type' => 'radio', 'label' => $bt('¿Ya tiene un dominio (por ejemplo, minegocio.com)?', 'Do you already have a domain (for example, mybusiness.com)?'), 'options' => $yesNo],
                'dominio_actual' => ['type' => 'text', 'label' => $bt('Si ya lo tiene, ¿cuál es?', 'If you have one, what is it?')],
                'dominio_opciones' => ['type' => 'textarea', 'label' => $bt('Si no tiene dominio, escriba algunas opciones que le gustarían', 'If you don\'t have a domain, list some options you\'d like'), 'hint' => $bt('Una por línea, en orden de preferencia. Lo registramos a nombre de usted.', 'One per line, in order of preference. We register it in your name.')],
                'hosting_tiene' => ['type' => 'radio', 'label' => $bt('¿Tiene actualmente una página web o hosting con otro proveedor?', 'Do you currently have a website or hosting with another provider?'), 'hint' => $bt('Solo para saber si hay que mover algo; el hosting nuevo va incluido en su paquete.', 'Only so we know whether something needs to be moved; the new hosting is included in your package.'), 'options' => $yesNo + ['no_seguro' => $bt('No estoy seguro', 'Not sure')]],
                'hosting_proveedor' => ['type' => 'text', 'label' => $bt('Proveedor actual', 'Current provider'), 'hint' => $bt('No escriba contraseñas aquí; si hacen falta accesos se los pediremos de forma segura.', 'Don\'t write passwords here; if we need access we will ask for it securely.')],
            ],
        ],

        'diseno_competencia' => [
            'title' => $bt('Diseño y competencia', 'Design and competitors'),
            'desc' => $bt('El estilo visual que busca y con quién compite.', 'The visual style you\'re after and who you compete with.'),
            'fields' => [
                'diseno_preferencia' => ['type' => 'textarea', 'label' => $bt('¿Tiene alguna preferencia de diseño?', 'Do you have a design preference?'), 'hint' => $bt('Ejemplos: moderno, elegante, minimalista, corporativo, industrial, premium, tradicional, colorido.', 'Examples: modern, elegant, minimalist, corporate, industrial, premium, traditional, colorful.')],
                'color_principal' => ['type' => 'text', 'label' => $bt('Color principal', 'Primary color')],
                'color_secundario' => ['type' => 'text', 'label' => $bt('Color secundario', 'Secondary color')],
                'color_otros' => ['type' => 'text', 'label' => $bt('Otros colores', 'Other colors')],
                'referencias' => ['type' => 'list', 'label' => $bt('Sitios web que le gustan (de referencia)', 'Websites you like (for reference)'), 'item_label' => $bt('Referencia', 'Reference'), 'count' => 5],
                'diseno_que_gusta' => ['type' => 'textarea', 'label' => $bt('¿Qué le gusta de esos sitios?', 'What do you like about those sites?')],
                'competidores' => ['except' => ['landing'], 'type' => 'repeat', 'item_label' => $bt('Competidor', 'Competitor'), 'count' => 3, 'subfields' => [
                    'nombre' => ['type' => 'text', 'label' => $bt('Nombre', 'Name')],
                    'website' => ['type' => 'text', 'label' => $bt('Sitio web', 'Website')],
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
                'tienda_variantes' => ['type' => 'radio', 'label' => $bt('¿Sus productos tienen variantes (talla, color, tamaño)?', 'Do your products have variants (size, color, etc.)?'), 'options' => $yesNo],
                'tienda_info_productos' => ['type' => 'radio', 'label' => $bt('¿Tiene lista la información de sus productos (fotos, descripciones, precios)?', 'Is your product information ready (photos, descriptions, prices)?'), 'options' => [
                    'completa' => $bt('Sí, completa', 'Yes, complete'),
                    'parcial' => $bt('Parcialmente', 'Partially'),
                    'no' => $bt('No, necesito ayuda', 'No, I need help'),
                ]],
                'tienda_pagos' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('¿Qué formas de pago desea aceptar?', 'Which payment methods do you want to accept?'), 'options' => [
                    'tarjeta' => $bt('Tarjeta de crédito / débito', 'Credit / debit card'),
                    'paypal' => $bt('PayPal', 'PayPal'),
                    'apple_google_pay' => $bt('Apple Pay / Google Pay', 'Apple Pay / Google Pay'),
                    'zelle' => $bt('Zelle', 'Zelle'),
                    'efectivo_entrega' => $bt('Efectivo al recoger / entregar', 'Cash on pickup / delivery'),
                ]],
                'tienda_procesador' => ['type' => 'text', 'label' => $bt('¿Ya tiene cuenta con algún procesador de pagos?', 'Do you already have a payment processor account?'), 'hint' => $bt('Ejemplo: Stripe, Square, PayPal, Clover.', 'Example: Stripe, Square, PayPal, Clover.')],
                'tienda_entrega' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('¿Cómo entregará sus productos?', 'How will you deliver your products?'), 'options' => [
                    'envio_usa' => $bt('Envío a todo EE. UU.', 'Shipping anywhere in the US'),
                    'entrega_local' => $bt('Entrega local', 'Local delivery'),
                    'recoger' => $bt('Recoger en tienda', 'In-store pickup'),
                    'digital' => $bt('Productos digitales / descargables', 'Digital / downloadable products'),
                ]],
                'tienda_envio_costos' => ['type' => 'textarea', 'label' => $bt('¿Cómo cobra el envío?', 'How do you charge for shipping?'), 'hint' => $bt('Ejemplo: tarifa fija de $10, gratis en compras mayores a $75, según peso.', 'Example: flat $10 rate, free over $75, by weight.')],
                'tienda_inventario' => ['type' => 'radio', 'label' => $bt('¿Necesita control de inventario en la tienda?', 'Do you need inventory tracking in the store?'), 'options' => $yesNo],
                'tienda_politicas' => ['type' => 'textarea', 'label' => $bt('Políticas de devolución y cambios', 'Return and exchange policy'), 'hint' => $bt('Si ya las tiene, péguelas aquí o indique dónde encontrarlas.', 'If you already have one, paste it here or tell us where to find it.')],
            ],
        ],

        'premium' => [
            'only' => ['premium'],
            'title' => $bt('Funciones Premium', 'Premium features'),
            'desc' => $bt('Blog, formularios avanzados, integraciones, SEO local y animaciones incluidos en su paquete.', 'Blog, advanced forms, integrations, local SEO and animations included in your package.'),
            'fields' => [
                'blog_quien_escribe' => ['type' => 'radio', 'label' => $bt('Blog: ¿quién escribirá los artículos?', 'Blog: who will write the articles?'), 'options' => [
                    'cliente' => $bt('Nosotros (el negocio)', 'We will (the business)'),
                    'ninubo' => $bt('Quisiera cotizar redacción profesional', 'I\'d like a quote for professional writing'),
                    'mixto' => $bt('Una combinación', 'A combination'),
                ]],
                'blog_temas' => ['type' => 'textarea', 'label' => $bt('Temas sobre los que le gustaría publicar', 'Topics you would like to publish about'), 'hint' => $bt('Ejemplo: consejos de mantenimiento, proyectos recientes, temporada de huracanes.', 'Example: maintenance tips, recent projects, hurricane season.')],
                'formularios_avanzados' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('¿Qué formularios avanzados necesita?', 'Which advanced forms do you need?'), 'options' => [
                    'cotizacion' => $bt('Solicitud de cotización por pasos', 'Multi-step quote request'),
                    'citas' => $bt('Solicitud de cita', 'Appointment request'),
                    'archivos' => $bt('Envío de fotos o archivos', 'Photo / file upload'),
                    'empleo' => $bt('Solicitud de empleo', 'Job application'),
                    'encuesta' => $bt('Encuesta / evaluación', 'Survey / assessment'),
                ]],
                'integraciones' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('¿Con qué herramientas debe conectarse el sitio?', 'Which tools should the website connect to?'), 'hint' => $bt('Incluye integraciones básicas; las más complejas se cotizan aparte.', 'Basic integrations are included; complex ones are quoted separately.'), 'options' => [
                    'agenda' => $bt('Agenda en línea (Calendly, Square, Google Calendar)', 'Online booking (Calendly, Square, Google Calendar)'),
                    'crm' => $bt('CRM', 'CRM'),
                    'email_marketing' => $bt('Email marketing (Mailchimp, etc.)', 'Email marketing (Mailchimp, etc.)'),
                    'pagos' => $bt('Pagos / depósitos en línea', 'Online payments / deposits'),
                    'chat' => $bt('Chat en vivo', 'Live chat'),
                    'resenas_widget' => $bt('Widget de reseñas de Google', 'Google reviews widget'),
                ]],
                'herramientas_actuales' => ['type' => 'textarea', 'label' => $bt('¿Qué programas o sistemas usa hoy su negocio?', 'What software or systems does your business use today?'), 'hint' => $bt('Nombre del programa y para qué lo usa. Nos ayuda a planear las integraciones.', 'Program name and what you use it for. It helps us plan the integrations.')],
                'gbp_acceso' => ['type' => 'radio', 'label' => $bt('SEO local: ¿puede darnos acceso a su Google Business Profile?', 'Local SEO: can you give us access to your Google Business Profile?'), 'options' => [
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
            'title' => $bt('Funciones y documentos legales', 'Features and legal documents'),
            'desc' => $bt('Extras para su sitio y los documentos legales que ya tenga.', 'Extras for your website and any legal documents you already have.'),
            'fields' => [
                'funcionalidades' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('¿Desea alguna de estas funciones?', 'Do you want any of these features?'), 'hint' => $bt('Si necesita algo que no aparece, escríbalo en "Otro" y se lo cotizamos.', 'If you need something that isn\'t listed, write it under "Other" and we\'ll quote it.'), 'options' => [
                    'cotizacion_online' => $bt('Solicitud de cotización', 'Quote request form'),
                    'whatsapp' => $bt('Botón de WhatsApp', 'WhatsApp button'),
                    'chat' => $bt('Chat', 'Chat'),
                    'galeria' => $bt('Galería', 'Gallery'),
                    'reviews' => $bt('Reseñas', 'Reviews'),
                    'newsletter' => $bt('Suscripción a newsletter', 'Newsletter sign-up'),
                    'redes_sociales' => $bt('Integración con redes sociales', 'Social media integration'),
                    'citas' => $bt('Reservación de citas', 'Appointment booking'),
                    'calendario' => $bt('Calendario', 'Calendar'),
                    'blog' => $bt('Blog', 'Blog'),
                    // Not offered any more (store = its own package; the rest are quoted on request); kept for old answers.
                    'ecommerce' => $bt('E-commerce', 'E-commerce'),
                    'pagos_online' => $bt('Pagos en línea', 'Online payments'),
                    'membresias' => $bt('Membresías', 'Memberships'),
                    'area_privada' => $bt('Área privada para clientes', 'Private client area'),
                    'crm' => $bt('Integración con CRM', 'CRM integration'),
                ], 'options_only' => [
                    'citas' => ['premium'], 'calendario' => ['premium'], 'blog' => ['premium'],
                    'ecommerce' => [], 'pagos_online' => [], 'membresias' => [], 'area_privada' => [], 'crm' => [],
                ]],
                'info_legal' => ['type' => 'checkboxes', 'label' => $bt('¿Cuenta con alguno de estos documentos?', 'Do you have any of these documents?'), 'options' => [
                    'privacy_policy' => $bt('Aviso de privacidad (Privacy Policy)', 'Privacy Policy'),
                    'terms' => $bt('Términos y condiciones', 'Terms & Conditions'),
                    'cookie_policy' => $bt('Política de cookies', 'Cookie Policy'),
                    'disclaimer' => $bt('Aviso legal (Disclaimer)', 'Disclaimer'),
                    'ninguno' => $bt('No tengo ninguno', 'I don\'t have any'),
                ]],
                'legal_link' => ['type' => 'text', 'label' => $bt('Si ya los tiene, comparta un link a los documentos', 'If you have them, share a link to the documents')],
            ],
        ],

        'adicional_pendiente' => [
            'title' => $bt('Información adicional', 'Additional information'),
            'desc' => $bt('Cualquier otro detalle y lo que todavía le falta enviarnos.', 'Anything else, and what you still need to send us.'),
            'fields' => [
                'info_adicional' => ['type' => 'textarea', 'label' => $bt('¿Hay algo más que le gustaría incluir en su página?', 'Is there anything else you\'d like on your website?')],
                'material_pendiente' => ['type' => 'checkboxes', 'other' => true, 'label' => $bt('Marque lo que todavía necesita enviarnos', 'Mark what you still need to send us'), 'options' => [
                    'logo' => $bt('Logo', 'Logo'),
                    'fotos' => $bt('Fotografías', 'Photos'),
                    'textos' => $bt('Textos', 'Copy / text'),
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
            'desc' => $bt('Revise qué secciones completó. No es necesario tenerlo todo: lo que falte lo vemos juntos. Antes de empezar el diseño le confirmaremos estructura, páginas, contenido, estilo y fecha estimada de entrega.', 'Check which sections you completed. You don\'t need to have everything: we\'ll go over anything missing together. Before starting the design we will confirm the structure, pages, content, style and estimated delivery date with you.'),
            'summary' => true,
            'fields' => [
                'confirmacion' => ['type' => 'checkboxes', 'required' => true, 'label' => $bt('Confirmación', 'Confirmation'), 'options' => [
                    'confirmado' => $bt('Confirmo que la información proporcionada es correcta hasta donde la tengo disponible.', 'I confirm that the information provided is correct to the best of my knowledge.'),
                ]],
            ],
        ],
    ];
}

/**
 * Brief profiles: each catalog package (packages.brief_profile) picks one, and the client only sees that
 * profile's questions. No profile (briefs created before profiles) = everything without 'only' (like 'business').
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
        $fields = [];
        foreach ($step['fields'] as $name => $field) {
            if (ninubo_brief_applies($field, $profile)) {
                unset($field['only'], $field['except']);
                $fields[$name] = ninubo_brief_resolve_field($field, $lang, $profile);
            }
        }
        $resolved[$stepKey] = [
            'title' => ninubo_brief_resolve_text($step['title'], $lang),
            'desc' => isset($step['desc']) ? ninubo_brief_resolve_text($step['desc'], $lang) : '',
            'summary' => !empty($step['summary']),
            'fields' => $fields,
        ];
    }

    $cache[$cacheKey] = $resolved;
    return $resolved;
}

/**
 * Labels for answer keys that were asked by earlier versions of the form and are no longer in the schema,
 * so the CRM can still show those answers with a readable label.
 */
function ninubo_brief_legacy_labels(): array {
    return [
        'anio_inicio' => 'Year the business started',
        'whatsapp' => 'WhatsApp',
        'direccion' => 'Physical address',
        'ciudad_estado' => 'City / State',
        'horario_atencion' => 'Business hours',
        'areas_atender' => 'Cities or areas to serve',
        'diferencia_empresa' => 'What makes the company different',
        'seo_ciudades' => 'Cities to appear in on Google',
        'seo_local_ciudades' => 'Local SEO priority cities',
        'hosting_usuario' => 'Hosting username / access',
        'correos_desea' => 'Wants email addresses with the domain',
        'correos_lista' => 'Email addresses to create',
    ];
}
