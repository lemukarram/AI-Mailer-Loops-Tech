<?php

/**
 * Basic .env Loader for Core PHP (Zero dependencies)
 */
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

// Load .env if it exists
loadEnv(__DIR__ . '/../.env');

// Configuration settings
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'aimailsaas');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root_password');

// Default key if not in env
define('ADMIN_KEY', getenv('ADMIN_KEY') ?: 'SECURE_ADMIN_KEY');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080/');
