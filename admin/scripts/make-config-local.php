<?php
/**
 * Creates includes/config.local.php on a server from the PREVIOUS (pre-Git) production files, so the real
 * secret, setup hash and MySQL password never have to be typed or shown on screen.
 *
 *   php admin/scripts/make-config-local.php --config=/path/to/old/config.php --db=/path/to/old/db.php [--base=] [--force]
 *
 * - NINUBO_SECRET / NINUBO_ADMIN_PASSWORD_HASH are read from the old config.php (a new secret is generated if none).
 * - MySQL host / name / user / password are read from the old db.php (literal values or getenv(...) ?: 'default').
 * - Prints only a masked summary. Refuses to overwrite an existing config.local.php without --force.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$opts = getopt('', ['config:', 'db:', 'base::', 'force']);
$root = dirname(__DIR__, 2);
$target = $root . '/includes/config.local.php';

if (is_file($target) && !isset($opts['force'])) {
    fwrite(STDERR, "includes/config.local.php already exists. Use --force to replace it.\n");
    exit(1);
}

$configText = isset($opts['config']) && is_file($opts['config']) ? (string)file_get_contents($opts['config']) : '';
$dbText = isset($opts['db']) && is_file($opts['db']) ? (string)file_get_contents($opts['db']) : '';
if ($configText === '') {
    fwrite(STDERR, "Warning: old config.php not found/readable (--config). A new secret will be generated and the setup hash left empty.\n");
}
if ($dbText === '') {
    fwrite(STDERR, "Warning: old db.php not found/readable (--db). MySQL values will use the defaults; edit the file afterwards.\n");
}

/** Value of a PHP string literal ('…' or "…") captured by the regexes below. */
$unquote = static function (string $literal): string {
    $q = $literal[0];
    $body = substr($literal, 1, -1);
    return $q === "'" ? str_replace(["\\\\", "\\'"], ["\\", "'"], $body) : stripcslashes($body);
};
$lit = "('(?:[^'\\\\]|\\\\.)*'|\"(?:[^\"\\\\]|\\\\.)*\")";

/** Last string literal inside define('NAME', …) — covers define('X', 'v') and define('X', getenv('X') ?: 'v'). */
$fromDefine = static function (string $text, string $name) use ($lit, $unquote): ?string {
    if (!preg_match("/define\\(\\s*['\"]" . preg_quote($name, '/') . "['\"]\\s*,(.*?)\\);/s", $text, $m)) {
        return null;
    }
    if (!preg_match_all("/{$lit}/", $m[1], $all) || !$all[1]) {
        return null;
    }
    $values = array_map($unquote, $all[1]);
    $values = array_values(array_filter($values, static fn($v) => $v !== $name));
    return $values ? end($values) : null;
};

/** $var = 'v'; or $var = getenv('X') ?: 'v'; in db.php */
$fromVar = static function (string $text, string $var) use ($lit, $unquote): ?string {
    if (!preg_match('/\$' . preg_quote($var, '/') . '\s*=\s*(.*?);/s', $text, $m)) {
        return null;
    }
    if (!preg_match_all("/{$lit}/", $m[1], $all) || !$all[1]) {
        return null;
    }
    $values = array_map($unquote, $all[1]);
    $values = array_values(array_filter($values, static fn($v) => !str_starts_with($v, 'NINUBO_DB_')));
    return $values ? end($values) : null;
};

$secret = $fromDefine($configText, 'NINUBO_SECRET');
$generated = false;
if ($secret === null || $secret === '' || str_starts_with($secret, 'REMOVED')) {
    $secret = bin2hex(random_bytes(32));
    $generated = true;
}
$hash = $fromDefine($configText, 'NINUBO_ADMIN_PASSWORD_HASH') ?? '';
if (!preg_match('/^\$(2[aby]|argon2i|argon2id)\$/', $hash)) {
    $hash = '';
}

$db = [
    'NINUBO_DB_HOST' => $fromVar($dbText, 'host') ?? 'localhost',
    'NINUBO_DB_NAME' => $fromVar($dbText, 'name') ?? 'ninubo',
    'NINUBO_DB_USER' => $fromVar($dbText, 'user') ?? 'root',
    'NINUBO_DB_PASS' => $fromVar($dbText, 'pass') ?? '',
];
$base = array_key_exists('base', $opts) ? (string)$opts['base'] : '';

$php = "<?php\n/**\n * Server configuration — NOT in Git. Generated " . date('Y-m-d H:i') . " by admin/scripts/make-config-local.php.\n"
    . " * Template: includes/config.local.example.php\n */\n\n"
    . "define('NINUBO_BASE', " . var_export($base, true) . ");\n"
    . "define('NINUBO_SECRET', " . var_export($secret, true) . ");\n"
    . "define('NINUBO_ADMIN_PASSWORD_HASH', " . var_export($hash, true) . ");\n\n";
foreach ($db as $k => $v) {
    $php .= "define('{$k}', " . var_export($v, true) . ");\n";
}

if (file_put_contents($target, $php) === false) {
    fwrite(STDERR, "Could not write {$target}\n");
    exit(1);
}
@chmod($target, 0640);

$mask = static fn(string $v): string => $v === '' ? '(empty)' : str_repeat('*', min(8, strlen($v))) . ' (' . strlen($v) . ' chars)';
echo "Wrote includes/config.local.php\n";
echo "  NINUBO_BASE                 = '" . $base . "'\n";
echo "  NINUBO_SECRET               = " . ($generated ? 'NEW random secret generated' : 'copied from old config.php ' . $mask($secret)) . "\n";
echo "  NINUBO_ADMIN_PASSWORD_HASH  = " . ($hash !== '' ? 'copied (' . substr($hash, 0, 7) . '…)' : 'EMPTY — /admin/setup will not work until it is set') . "\n";
echo "  NINUBO_DB_HOST              = " . $db['NINUBO_DB_HOST'] . "\n";
echo "  NINUBO_DB_NAME              = " . $db['NINUBO_DB_NAME'] . "\n";
echo "  NINUBO_DB_USER              = " . $db['NINUBO_DB_USER'] . "\n";
echo "  NINUBO_DB_PASS              = " . $mask($db['NINUBO_DB_PASS']) . "\n";
