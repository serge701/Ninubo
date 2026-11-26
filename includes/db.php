<?php
/**
 * Conexión PDO a MySQL (briefs de clientes y CRM).
 * Credenciales: constantes NINUBO_DB_* en includes/config.local.php (no está en Git), o variables de entorno
 * con el mismo nombre; si no hay ninguna, los valores por defecto de XAMPP (root sin contraseña).
 */

function ninubo_db_setting(string $name, string $default): string {
    if (defined($name)) {
        return (string)constant($name);
    }
    $env = getenv($name);
    return $env !== false && $env !== '' ? $env : $default;
}

function ninubo_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = ninubo_db_setting('NINUBO_DB_HOST', 'localhost');
    $name = ninubo_db_setting('NINUBO_DB_NAME', 'ninubo');
    $user = ninubo_db_setting('NINUBO_DB_USER', 'root');
    $pass = ninubo_db_setting('NINUBO_DB_PASS', '');

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
