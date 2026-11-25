<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Capa 1: Honeypot — si viene relleno es un bot; respondemos éxito falso para no alertarlo
if (!empty($_POST['hp_url'])) {
    echo json_encode(['success' => true, 'message' => '¡Gracias! Recibimos tu mensaje.']);
    exit;
}

// Capa 2 y 3: Token firmado — valida origen y tiempo mínimo de 3 segundos
$token = $_POST['_t'] ?? '';
if (!ninubo_validate_token($token)) {
    echo json_encode(['success' => false, 'message' => 'Formulario inválido o expirado. Recarga la página e intenta de nuevo.']);
    exit;
}

$nombre  = trim($_POST['nombre'] ?? '');
$email   = trim($_POST['email'] ?? '');
$empresa = trim($_POST['empresa'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$asunto  = trim($_POST['asunto'] ?? 'Consulta desde ninubo.com');
$mensaje = trim($_POST['mensaje'] ?? '');

$errores = [];

if ($nombre === '' || mb_strlen($nombre) < 2) {
    $errores[] = 'nombre';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'correo electrónico';
}

if ($mensaje === '' || mb_strlen($mensaje) < 10) {
    $errores[] = 'mensaje';
}

if (!empty($errores)) {
    echo json_encode([
        'success' => false,
        'message' => 'Revisa los siguientes campos: ' . implode(', ', $errores) . '.'
    ]);
    exit;
}

$nombre_limpio   = str_replace(["\r", "\n"], '', $nombre);
$email_limpio    = str_replace(["\r", "\n"], '', $email);
$empresa_limpia  = str_replace(["\r", "\n"], '', $empresa);
$telefono_limpio = str_replace(["\r", "\n"], '', $telefono);
$asunto_limpio   = str_replace(["\r", "\n"], '', $asunto);

$destinatario = 'contacto@ninubo.com';
$asunto_mail = '[NINUBO] ' . ($asunto_limpio !== '' ? $asunto_limpio : 'Nuevo contacto');

$cuerpo = "Nuevo mensaje desde el formulario de contacto de ninubo.com\n\n";
$cuerpo .= "Nombre: {$nombre_limpio}\n";
$cuerpo .= "Correo: {$email_limpio}\n";
if ($empresa_limpia !== '') {
    $cuerpo .= "Empresa: {$empresa_limpia}\n";
}
if ($telefono_limpio !== '') {
    $cuerpo .= "Teléfono: {$telefono_limpio}\n";
}
$cuerpo .= "\nMensaje:\n{$mensaje}\n\n";
$cuerpo .= "---\nFecha: " . date('d/m/Y H:i') . "\n";
$cuerpo .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "\n";

$headers = "From: NINUBO Web <no-reply@ninubo.com>\r\n";
$headers .= "Reply-To: {$nombre_limpio} <{$email_limpio}>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$enviado = @mail($destinatario, $asunto_mail, $cuerpo, $headers);

if ($enviado) {
    echo json_encode([
        'success' => true,
        'message' => '¡Gracias, ' . $nombre_limpio . '! Recibimos tu mensaje y te contactaremos pronto.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible enviar el mensaje. Escríbenos directamente a contacto@ninubo.com o llama al 8995-08-3462.'
    ]);
}
