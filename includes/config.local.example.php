<?php
/**
 * Template for includes/config.local.php — the per-server configuration that is NOT committed to Git.
 *
 *   cp includes/config.local.example.php includes/config.local.php
 *
 * then fill in the values for this server. includes/config.php loads it automatically.
 * Every value can also come from an environment variable with the same name (set in the hosting panel).
 */

// URL path where the site lives: '' when it is the domain root (https://ninubo.com/),
// '/ninubo' for http://localhost/ninubo in XAMPP.
define('NINUBO_BASE', '');

// Long random secret that signs the contact-form tokens. Keep the SAME value once the site is live.
// Generate one with:  php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
define('NINUBO_SECRET', 'change-me');

// Hash of the old single admin-panel password. Only used once, by /admin/setup, to create the first CRM
// administrator. Generate with:  php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
define('NINUBO_ADMIN_PASSWORD_HASH', '');

// MySQL connection.
define('NINUBO_DB_HOST', 'localhost');
define('NINUBO_DB_NAME', 'ninubo');
define('NINUBO_DB_USER', 'ninubo_user');
define('NINUBO_DB_PASS', 'change-me');

// Optional: Google Analytics 4 measurement ID (G-XXXXXXXXXX). Loaded only after the visitor accepts cookies.
// define('NINUBO_GA_MEASUREMENT_ID', '');
