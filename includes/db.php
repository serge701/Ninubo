<?php
/**
 * Conexión PDO a MySQL, usada por el módulo de briefs de cliente.
 * En hosting real, define estas variables de entorno en el panel de control;
 * en XAMPP local caen en los valores por defecto (root sin contraseña).
 */

function ninubo_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = getenv('NINUBO_DB_HOST') ?: 'localhost';
    $name = getenv('NINUBO_DB_NAME') ?: 'ninubo';
    $user = getenv('NINUBO_DB_USER') ?: 'root';
    $pass = getenv('NINUBO_DB_PASS') ?: '';

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
