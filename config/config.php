<?php
// FILE: /config/config.php

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Application Settings
define('APP_NAME', $_ENV['APP_NAME'] ?? 'SplashCreator');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
define('BASE_URL', rtrim($_ENV['BASE_URL'] ?? '', '/'));
define('BASE_PATH', parse_url(BASE_URL, PHP_URL_PATH) ?: '/');

// Timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.gc_maxlifetime', $_ENV['SESSION_LIFETIME'] ?? 7200);

// File Upload Settings
define('MAX_UPLOAD_SIZE', $_ENV['MAX_UPLOAD_SIZE'] ?? 10485760);
define('UPLOAD_PATH', __DIR__ . '/../' . ($_ENV['UPLOAD_PATH'] ?? 'storage/uploads'));

// Security
SecurityHelper::preventClickjacking();
