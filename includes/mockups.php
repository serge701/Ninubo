<?php

function ninubo_mockup_map(): array {
    return [
        'hero-home.jpg'            => 'hero',
        'home-sistemas.jpg'        => 'systems',
        'home-web.jpg'             => 'web',
        'home-data.jpg'            => 'analytics',
        'home-equipo.jpg'          => 'team',
        'hero-sistemas.jpg'        => 'systems',
        'sistema-asistencia.jpg'   => 'attendance',
        'sistema-crm.jpg'          => 'crm',
        'sistema-erp.jpg'          => 'erp',
        'sistema-medapp.jpg'       => 'medapp',
        'hero-web.jpg'             => 'web',
        'web-diseno.jpg'           => 'design',
        'web-corporativo.jpg'      => 'corporate',
        'web-ecommerce.jpg'        => 'shop',
        'hero-data.jpg'            => 'analytics',
        'data-etl.jpg'             => 'etl',
        'data-bi.jpg'              => 'bi',
        'data-dashboard.jpg'       => 'sap',
        'portafolio-sap.jpg'       => 'sap',
        'portafolio-inventario.jpg'=> 'inventory',
        'portafolio-pos.jpg'       => 'pos',
        'portafolio-crm.jpg'       => 'crm',
        'portafolio-mantenimiento.jpg' => 'maintenance',
        'portafolio-iso.jpg'       => 'iso',
        'portafolio-logistica.jpg' => 'logistics',
        'genesissoluciones.jpg'    => 'corporate',
        'hmaindustrial.jpg'        => 'corporate',
        'cocinasfranco.jpg'        => 'corporate',
        'dentaldelcentro.jpg'      => 'corporate',
        'doctorposadas.jpg'        => 'corporate',
        'draerikatrejo.jpg'        => 'corporate',
        'tuiq.jpg'                 => 'corporate',
        'hero-contacto.jpg'        => 'team',
        'hero-portafolio.jpg'      => 'analytics',
        'hero-servicios.jpg'       => 'systems',
        'hero-capacitaciones.jpg'  => 'training',
        'hero-curso-sql.jpg'       => 'sql',
    ];
}

function ninubo_render_mockup(string $type): void {
    $fn = 'ninubo_mockup_' . $type;
    if (function_exists($fn)) {
        $fn();
        return;
    }
    ninubo_mockup_default();
}

function ninubo_mockup_window_start(string $title, string $extraClass = ''): void {
    echo '<div class="mockup-window' . ($extraClass ? ' ' . $extraClass : '') . '">';
    echo '<div class="mockup-titlebar"><span class="mockup-dot mockup-dot--r"></span><span class="mockup-dot mockup-dot--y"></span><span class="mockup-dot mockup-dot--g"></span><span class="mockup-title">' . htmlspecialchars($title) . '</span></div>';
    echo '<div class="mockup-body">';
}

function ninubo_mockup_window_end(): void {
    echo '</div></div>';
}

function ninubo_mockup_bars(array $heights, string $class = 'mockup-bars'): void {
    echo '<div class="' . $class . '">';
    foreach ($heights as $h) {
        echo '<span style="--h:' . (int)$h . '%"></span>';
    }
    echo '</div>';
}

function ninubo_mockup_default(): void {
    ninubo_mockup_window_start('ninubo — preview');
    echo '<p class="mockup-line mockup-line--muted"><span class="mockup-prompt">$</span> status --all</p>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('Sistemas activos', 'Systems active') . '</p>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('Datos sincronizados', 'Data synced') . '</p>';
    ninubo_mockup_bars([55, 72, 48, 88, 64, 91]);
    ninubo_mockup_window_end();
}

function ninubo_mockup_hero(): void {
    echo '<div class="visual-mockup visual-mockup--hero">';
    ninubo_mockup_window_start('ninubo — command center', 'mockup-window--hero');
    echo '<p class="mockup-line mockup-line--muted"><span class="mockup-prompt">$</span> ninubo deploy --client=' . ninubo_t('empresa', 'company') . '</p>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('CRM configurado', 'CRM configured') . '</p>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('ZKTECO sincronizado', 'ZKTECO synced') . '</p>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('Reportes Power BI activos', 'Power BI reports active') . '</p>';
    ninubo_mockup_bars([72, 45, 88, 61, 94, 78]);
    echo '<p class="mockup-line mockup-line--accent">→ ' . ninubo_t('Decisiones basadas en datos reales', 'Decisions based on real data') . '</p>';
    ninubo_mockup_window_end();
    echo '<div class="mockup-float mockup-float--1"><span class="mockup-float-label">' . ninubo_t('Asistencia', 'Attendance') . '</span><strong>248</strong><span>' . ninubo_t('checadas hoy', 'clocked in today') . '</span></div>';
    echo '<div class="mockup-float mockup-float--2"><span class="mockup-float-label">CRM</span><strong>12</strong><span>' . ninubo_t('cotizaciones', 'quotes') . '</span></div>';
    echo '</div>';
}

function ninubo_mockup_systems(): void {
    ninubo_mockup_window_start('NINUBO — ' . ninubo_t('módulos', 'modules'));
    echo '<div class="mockup-grid mockup-grid--2x2">';
    echo '<div class="mockup-tile mockup-tile--active"><span>CRM</span><small>' . ninubo_t('Clientes', 'Customers') . '</small></div>';
    echo '<div class="mockup-tile"><span>ERP</span><small>' . ninubo_t('Inventario', 'Inventory') . '</small></div>';
    echo '<div class="mockup-tile"><span>RH</span><small>' . ninubo_t('Asistencia', 'Attendance') . '</small></div>';
    echo '<div class="mockup-tile"><span>MEDAPP</span><small>' . ninubo_t('Clínicas', 'Clinics') . '</small></div>';
    echo '</div>';
    echo '<div class="mockup-stat-row"><div><small>' . ninubo_t('Usuarios', 'Users') . '</small><strong>156</strong></div><div><small>' . ninubo_t('Módulos', 'Modules') . '</small><strong>8</strong></div><div><small>Uptime</small><strong>99.9%</strong></div></div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_web(): void {
    echo '<div class="visual-mockup visual-mockup--browser">';
    echo '<div class="mockup-browser">';
    echo '<div class="mockup-browser-bar"><span class="mockup-dot mockup-dot--r"></span><span class="mockup-dot mockup-dot--y"></span><span class="mockup-dot mockup-dot--g"></span><span class="mockup-url">' . ninubo_t('www.tu-negocio.com', 'www.your-business.com') . '</span></div>';
    echo '<div class="mockup-browser-page">';
    echo '<div class="mockup-site-nav"></div>';
    echo '<div class="mockup-site-hero"><div class="mockup-site-title"></div><div class="mockup-site-text"></div><div class="mockup-site-btn"></div></div>';
    echo '<div class="mockup-site-cards"><span></span><span></span><span></span></div>';
    echo '</div></div></div>';
}

function ninubo_mockup_analytics(): void {
    ninubo_mockup_window_start('Power BI — ' . ninubo_t('ejecutivo', 'executive'));
    echo '<div class="mockup-kpi-row"><div class="mockup-kpi"><small>' . ninubo_t('Ventas', 'Sales') . '</small><strong>$2.4M</strong></div><div class="mockup-kpi"><small>OEE</small><strong>87%</strong></div><div class="mockup-kpi mockup-kpi--up"><small>' . ninubo_t('Meta', 'Target') . '</small><strong>+12%</strong></div></div>';
    ninubo_mockup_bars([40, 65, 52, 78, 90, 72, 85]);
    echo '<div class="mockup-legend"><span class="mockup-legend-item mockup-legend-item--a">' . ninubo_t('Producción', 'Production') . '</span><span class="mockup-legend-item mockup-legend-item--b">' . ninubo_t('Calidad', 'Quality') . '</span></div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_team(): void {
    echo '<div class="visual-mockup visual-mockup--team">';
    ninubo_mockup_window_start('NINUBO — ' . ninubo_t('soporte 24/7', '24/7 support'));
    echo '<div class="mockup-chat">';
    echo '<div class="mockup-chat-msg mockup-chat-msg--in"><span>' . ninubo_t('¿Reporte de asistencia listo?', 'Is the attendance report ready?') . '</span></div>';
    echo '<div class="mockup-chat-msg mockup-chat-msg--out"><span>✓ ' . ninubo_t('Exportado a nómina', 'Exported to payroll') . '</span></div>';
    echo '<div class="mockup-chat-msg mockup-chat-msg--in"><span>' . ninubo_t('Dashboard SAP actualizado', 'SAP dashboard updated') . '</span></div>';
    echo '</div>';
    echo '<div class="mockup-avatars"><span>A</span><span>S</span><span>N</span><span>+5</span></div>';
    ninubo_mockup_window_end();
    echo '</div>';
}

function ninubo_mockup_attendance(): void {
    ninubo_mockup_window_start(ninubo_t('Asistencia', 'Attendance') . ' — ZKTECO');
    echo '<div class="mockup-split">';
    echo '<div class="mockup-device"><div class="mockup-device-screen"><span class="mockup-fingerprint"></span><small>' . ninubo_t('Huella OK', 'Fingerprint OK') . '</small></div><span class="mockup-device-label">ZKTECO</span></div>';
    echo '<div class="mockup-list">';
    echo '<div class="mockup-list-item mockup-list-item--ok"><span>08:02</span><strong>María G.</strong><em>' . ninubo_t('Entrada', 'Check-in') . '</em></div>';
    echo '<div class="mockup-list-item mockup-list-item--ok"><span>08:05</span><strong>Juan R.</strong><em>' . ninubo_t('Entrada', 'Check-in') . '</em></div>';
    echo '<div class="mockup-list-item mockup-list-item--warn"><span>08:18</span><strong>Carlos M.</strong><em>' . ninubo_t('Retardo', 'Late') . '</em></div>';
    echo '</div></div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_crm(): void {
    ninubo_mockup_window_start('CRM — pipeline');
    echo '<div class="mockup-pipeline">';
    echo '<div class="mockup-pipeline-col"><small>Lead</small><div class="mockup-card-sm">AutoParts MX</div><div class="mockup-card-sm">' . ninubo_t('Logística Norte', 'North Logistics') . '</div></div>';
    echo '<div class="mockup-pipeline-col"><small>' . ninubo_t('Cotización', 'Quote') . '</small><div class="mockup-card-sm mockup-card-sm--hot">Industrias RE</div></div>';
    echo '<div class="mockup-pipeline-col"><small>' . ninubo_t('Cierre', 'Close') . '</small><div class="mockup-card-sm mockup-card-sm--win">Grupo Delta</div></div>';
    echo '</div>';
    echo '<div class="mockup-stat-row"><div><small>Pipeline</small><strong>$840K</strong></div><div><small>' . ninubo_t('Conversión', 'Conversion') . '</small><strong>34%</strong></div></div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_erp(): void {
    ninubo_mockup_window_start('ERP — ' . ninubo_t('inventario', 'inventory'));
    echo '<div class="mockup-table">';
    echo '<div class="mockup-table-head"><span>SKU</span><span>' . ninubo_t('Producto', 'Product') . '</span><span>Stock</span></div>';
    echo '<div class="mockup-table-row"><span>A-1042</span><span>' . ninubo_t('Tornillería M8', 'M8 Hardware') . '</span><span class="mockup-badge mockup-badge--ok">1,240</span></div>';
    echo '<div class="mockup-table-row"><span>B-8831</span><span>' . ninubo_t('Empaque export.', 'Export packaging') . '</span><span class="mockup-badge mockup-badge--warn">87</span></div>';
    echo '<div class="mockup-table-row"><span>C-2200</span><span>' . ninubo_t('Etiqueta QR', 'QR Label') . '</span><span class="mockup-badge mockup-badge--ok">5,600</span></div>';
    echo '</div>';
    echo '<div class="mockup-qr-row"><span class="mockup-qr"></span><span class="mockup-qr"></span><span class="mockup-qr"></span><small>' . ninubo_t('Embarque #8842', 'Shipment #8842') . '</small></div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_medapp(): void {
    ninubo_mockup_window_start('MEDAPP — ' . ninubo_t('consultorio', 'clinic'));
    echo '<div class="mockup-split">';
    echo '<div class="mockup-calendar">';
    echo '<div class="mockup-cal-header">' . ninubo_t('Agenda — Hoy', 'Schedule — Today') . '</div>';
    echo '<div class="mockup-cal-slot mockup-cal-slot--busy">09:00 · Dra. López</div>';
    echo '<div class="mockup-cal-slot">10:30 · ' . ninubo_t('Disponible', 'Available') . '</div>';
    echo '<div class="mockup-cal-slot mockup-cal-slot--busy">11:00 · Dr. Pérez</div>';
    echo '</div>';
    echo '<div class="mockup-record"><div class="mockup-record-head">' . ninubo_t('Expediente #1842', 'Record #1842') . '</div><div class="mockup-record-line"></div><div class="mockup-record-line mockup-record-line--short"></div><div class="mockup-rx">' . ninubo_t('Rx digital', 'Digital Rx') . '</div></div>';
    echo '</div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_design(): void {
    ninubo_mockup_window_start('UI/UX — ' . ninubo_t('diseño', 'design'));
    echo '<div class="mockup-wireframe">';
    echo '<div class="mockup-wf-block mockup-wf-block--nav"></div>';
    echo '<div class="mockup-wf-block mockup-wf-block--hero"></div>';
    echo '<div class="mockup-wf-row"><span></span><span></span><span></span></div>';
    echo '</div>';
    echo '<div class="mockup-palette"><span style="background:#0ea5e9"></span><span style="background:#6366f1"></span><span style="background:#22d3ee"></span><span style="background:#111827"></span></div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_corporate(): void {
    ninubo_mockup_web();
}

function ninubo_mockup_shop(): void {
    ninubo_mockup_window_start(ninubo_t('Tienda en línea', 'Online store'));
    echo '<div class="mockup-shop-grid">';
    for ($i = 0; $i < 4; $i++) {
        echo '<div class="mockup-shop-item"><div class="mockup-shop-img"></div><div class="mockup-shop-line"></div><div class="mockup-shop-price"></div></div>';
    }
    echo '</div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_etl(): void {
    echo '<div class="visual-mockup visual-mockup--etl">';
    echo '<div class="mockup-flow">';
    echo '<div class="mockup-flow-node"><span>SAP</span><small>Extract</small></div>';
    echo '<div class="mockup-flow-arrow">→</div>';
    echo '<div class="mockup-flow-node mockup-flow-node--accent"><span>ETL</span><small>Transform</small></div>';
    echo '<div class="mockup-flow-arrow">→</div>';
    echo '<div class="mockup-flow-node"><span>BI</span><small>Load</small></div>';
    echo '</div>';
    ninubo_mockup_window_start('Pipeline — ' . ninubo_t('automatizado', 'automated'));
    echo '<p class="mockup-line mockup-line--muted">' . ninubo_t('Última corrida: hace 15 min', 'Last run: 15 min ago') . '</p>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('24,891 registros procesados', '24,891 records processed') . '</p>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('0 errores de validación', '0 validation errors') . '</p>';
    ninubo_mockup_window_end();
    echo '</div>';
}

function ninubo_mockup_bi(): void {
    ninubo_mockup_analytics();
}

function ninubo_mockup_sap(): void {
    ninubo_mockup_window_start('SAP — ' . ninubo_t('dashboard planta', 'plant dashboard'));
    echo '<div class="mockup-alert mockup-alert--warn">⚠ ' . ninubo_t('Corte material: Línea 3', 'Material shortage: Line 3') . '</div>';
    echo '<div class="mockup-kpi-row"><div class="mockup-kpi"><small>' . ninubo_t('Cortos', 'Shortages') . '</small><strong>7</strong></div><div class="mockup-kpi"><small>' . ninubo_t('Resueltos', 'Resolved') . '</small><strong>4</strong></div><div class="mockup-kpi mockup-kpi--up"><small>' . ninubo_t('Tiempo', 'Time') . '</small><strong>-18%</strong></div></div>';
    ninubo_mockup_bars([90, 45, 72, 30, 85, 55]);
    echo '<p class="mockup-line mockup-line--accent">' . ninubo_t('Conectado a SAP MM · PP', 'Connected to SAP MM · PP') . '</p>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_inventory(): void {
    ninubo_mockup_erp();
}

function ninubo_mockup_pos(): void {
    ninubo_mockup_window_start('POS — ' . ninubo_t('comanda', 'kitchen ticket'));
    echo '<div class="mockup-split mockup-split--pos">';
    echo '<div class="mockup-order"><div class="mockup-order-item"><span>' . ninubo_t('Mesa 12', 'Table 12') . '</span><strong>2× ' . ninubo_t('Arrachera', 'Skirt steak') . '</strong></div><div class="mockup-order-item"><span>' . ninubo_t('Mesa 08', 'Table 08') . '</span><strong>1× Pasta</strong></div><div class="mockup-order-item mockup-order-item--new"><span>' . ninubo_t('Nuevo', 'New') . '</span><strong>3× Tacos</strong></div></div>';
    echo '<div class="mockup-kitchen"><div class="mockup-kitchen-title">' . ninubo_t('Cocina', 'Kitchen') . '</div><div class="mockup-kitchen-ticket mockup-kitchen-ticket--ready">' . ninubo_t('Listo ✓', 'Ready ✓') . '</div><div class="mockup-kitchen-ticket">' . ninubo_t('Preparando…', 'Preparing…') . '</div></div>';
    echo '</div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_maintenance(): void {
    ninubo_mockup_window_start(ninubo_t('Mantenimiento — máquinas', 'Maintenance — machines'));
    echo '<div class="mockup-machine-list">';
    echo '<div class="mockup-machine mockup-machine--ok"><span>CNC-01</span><em>' . ninubo_t('Preventivo · 15 Jun', 'Preventive · Jun 15') . '</em></div>';
    echo '<div class="mockup-machine mockup-machine--warn"><span>PRENSA-04</span><em>' . ninubo_t('Correctivo · Hoy', 'Corrective · Today') . '</em></div>';
    echo '<div class="mockup-machine mockup-machine--ok"><span>INJECT-12</span><em>' . ninubo_t('OK · 82 días', 'OK · 82 days') . '</em></div>';
    echo '</div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_iso(): void {
    ninubo_mockup_window_start('ISO — ' . ninubo_t('capacitación', 'training'));
    echo '<div class="mockup-progress-list">';
    echo '<div class="mockup-progress"><span>' . ninubo_t('Procedimiento ISO 9001', 'ISO 9001 Procedure') . '</span><div class="mockup-progress-bar"><span style="width:100%"></span></div></div>';
    echo '<div class="mockup-progress"><span>' . ninubo_t('Control de acceso', 'Access control') . '</span><div class="mockup-progress-bar"><span style="width:78%"></span></div></div>';
    echo '<div class="mockup-progress"><span>' . ninubo_t('Gestión vacaciones', 'Vacation management') . '</span><div class="mockup-progress-bar"><span style="width:92%"></span></div></div>';
    echo '</div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_training(): void {
    ninubo_mockup_window_start('NINUBO — ' . ninubo_t('capacitaciones', 'training'));
    echo '<div class="mockup-list">';
    echo '<div class="mockup-list-item mockup-list-item--ok"><span>01</span><strong>SQL — ' . ninubo_t('Consulta y Análisis', 'Querying & Analysis') . '</strong><em>' . ninubo_t('Disponible', 'Available') . '</em></div>';
    echo '<div class="mockup-list-item mockup-list-item--warn"><span>02</span><strong>' . ninubo_t('Excel Avanzado', 'Advanced Excel') . '</strong><em>' . ninubo_t('Próximamente', 'Coming soon') . '</em></div>';
    echo '<div class="mockup-list-item mockup-list-item--warn"><span>03</span><strong>Power BI — ' . ninubo_t('Reportes', 'Reporting') . '</strong><em>' . ninubo_t('Próximamente', 'Coming soon') . '</em></div>';
    echo '</div>';
    echo '<div class="mockup-stat-row"><div><small>' . ninubo_t('Modalidad', 'Format') . '</small><strong>' . ninubo_t('Presencial', 'In-person') . '</strong></div><div><small>' . ninubo_t('Formato', 'Structure') . '</small><strong>' . ninubo_t('Modular', 'Modular') . '</strong></div><div><small>' . ninubo_t('Grupo', 'Group') . '</small><strong>' . ninubo_t('A tu medida', 'Custom fit') . '</strong></div></div>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_sql(): void {
    ninubo_mockup_window_start('HeidiSQL — consulta.sql');
    echo '<p class="mockup-line mockup-line--muted">-- ' . ninubo_t('Ventas por cliente', 'Sales by customer') . '</p>';
    echo '<p class="mockup-line"><span class="mockup-prompt">SELECT</span>cliente, SUM(monto) AS total</p>';
    echo '<p class="mockup-line"><span class="mockup-prompt">FROM</span>ventas</p>';
    echo '<p class="mockup-line"><span class="mockup-prompt">GROUP BY</span>cliente <span class="mockup-prompt">ORDER BY</span>total DESC;</p>';
    echo '<div class="mockup-table">';
    echo '<div class="mockup-table-head"><span>' . ninubo_t('Cliente', 'Customer') . '</span><span>Total</span></div>';
    echo '<div class="mockup-table-row"><span>Grupo Delta</span><span class="mockup-badge mockup-badge--ok">$412,900</span></div>';
    echo '<div class="mockup-table-row"><span>Industrias RE</span><span class="mockup-badge mockup-badge--ok">$298,150</span></div>';
    echo '<div class="mockup-table-row"><span>AutoParts MX</span><span class="mockup-badge mockup-badge--warn">$176,300</span></div>';
    echo '</div>';
    echo '<p class="mockup-line mockup-line--ok">✓ ' . ninubo_t('3 filas obtenidas', '3 rows returned') . '</p>';
    ninubo_mockup_window_end();
}

function ninubo_mockup_logistics(): void {
    ninubo_mockup_window_start(ninubo_t('Logística — traslados', 'Logistics — transfers'));
    echo '<div class="mockup-route">';
    echo '<div class="mockup-route-point mockup-route-point--start"><span>' . ninubo_t('US Frontera', 'US Border') . '</span></div>';
    echo '<div class="mockup-route-line"></div>';
    echo '<div class="mockup-route-point mockup-route-point--mid"><span>' . ninubo_t('En tránsito', 'In transit') . '</span><small>' . ninubo_t('Urgente', 'Urgent') . '</small></div>';
    echo '<div class="mockup-route-line mockup-route-line--dashed"></div>';
    echo '<div class="mockup-route-point mockup-route-point--end"><span>' . ninubo_t('CD México', 'Mexico City') . '</span></div>';
    echo '</div>';
    echo '<div class="mockup-stat-row"><div><small>' . ninubo_t('Envíos hoy', 'Shipments today') . '</small><strong>23</strong></div><div><small>' . ninubo_t('En ruta', 'En route') . '</small><strong>8</strong></div></div>';
    ninubo_mockup_window_end();
}
