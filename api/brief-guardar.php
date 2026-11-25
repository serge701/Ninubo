<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/brief-schema.php';
require_once __DIR__ . '/../includes/brief-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$lang = ($_POST['lang'] ?? 'es') === 'en' ? 'en' : 'es';
$t = fn(string $es, string $en): string => $lang === 'en' ? $en : $es;

// Honeypot — si viene relleno es un bot; respondemos éxito falso para no alertarlo.
if (!empty($_POST['hp_url'])) {
    echo json_encode(['success' => true, 'message' => $t('Guardado.', 'Saved.')]);
    exit;
}

$token = trim($_POST['token'] ?? '');
if ($token === '') {
    echo json_encode(['success' => false, 'message' => $t('Enlace inválido.', 'Invalid link.')]);
    exit;
}

try {
    $pdo = ninubo_db();
    $stmt = $pdo->prepare('SELECT * FROM client_briefs WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $client = $stmt->fetch();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $t('No fue posible guardar. Intente de nuevo en unos minutos.', 'We could not save your progress. Please try again in a few minutes.')]);
    exit;
}

if (!$client) {
    echo json_encode(['success' => false, 'message' => $t('Enlace inválido o expirado.', 'Invalid or expired link.')]);
    exit;
}

if ($client['status'] === 'completado') {
    echo json_encode(['success' => false, 'message' => $t('Este formulario ya fue enviado. Si necesita corregir algo, contáctenos directamente.', 'This form has already been submitted. If you need to correct something, please contact us directly.')]);
    exit;
}

$action = ($_POST['action'] ?? 'draft') === 'submit' ? 'submit' : 'draft';

// El esquema se resuelve en cualquier idioma solo para validar — las opciones de
// checkboxes/radio usan claves estables independientes del idioma (ver brief-schema.php).
$schema = ninubo_brief_schema();
$stepCount = count($schema);
$currentStep = (int)($_POST['current_step'] ?? 1);
$currentStep = max(1, min($stepCount, $currentStep));

$data = ninubo_brief_collect($schema);
$json = json_encode($data, JSON_UNESCAPED_UNICODE);

try {
    if ($action === 'submit') {
        $stmt = $pdo->prepare('UPDATE client_briefs SET data = ?, current_step = ?, status = ?, lang = ?, completed_at = NOW() WHERE id = ?');
        $stmt->execute([$json, $currentStep, 'completado', $lang, $client['id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE client_briefs SET data = ?, current_step = ?, status = ?, lang = ? WHERE id = ?');
        $newStatus = $client['status'] === 'pendiente' ? 'en_progreso' : $client['status'];
        $stmt->execute([$json, $currentStep, $newStatus, $lang, $client['id']]);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $t('No fue posible guardar. Intente de nuevo en unos minutos.', 'We could not save your progress. Please try again in a few minutes.')]);
    exit;
}

if ($action === 'submit') {
    $destinatario = 'contacto@ninubo.com';
    $asunto = '[NINUBO] Brief completado: ' . $client['client_label'];
    $adminLink = ninubo_absolute_url('admin/brief-ver.php?id=' . $client['id'], 'es');
    $cuerpo = "El cliente \"{$client['client_label']}\" completó su formulario de información inicial.\n\n";
    $cuerpo .= "Ver respuestas: {$adminLink}\n";
    $cuerpo .= "Idioma usado por el cliente: " . ($lang === 'en' ? 'Inglés' : 'Español') . "\n";
    $cuerpo .= "Fecha: " . date('d/m/Y H:i') . "\n";
    $headers = "From: NINUBO Web <no-reply@ninubo.com>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    @mail($destinatario, $asunto, $cuerpo, $headers);

    echo json_encode(['success' => true, 'message' => $t('¡Gracias! Recibimos su información correctamente.', 'Thank you! We received your information successfully.')]);
    exit;
}

echo json_encode(['success' => true, 'message' => $t('Guardado.', 'Saved.')]);
