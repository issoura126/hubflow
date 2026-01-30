<?php
/**
 * N8N Pro - Configuration File
 * Database and Application Settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u372572592_N8N');
define('DB_USER', 'u372572592_N8N');
define('DB_PASS', '1@s~?^k5Q');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'N8N Pro');
define('APP_VERSION', '3.0.0');
define('BASE_URL', 'https://dreamcloudsleep.shop');
define('TIMEZONE', 'UTC');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('NODES_PATH', ROOT_PATH . '/nodes');

// Security
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('MAX_EXECUTION_TIME', 300); // 5 minutes
define('MEMORY_LIMIT', '256M');

// API Settings
define('API_RATE_LIMIT', 100); // requests per minute
define('WEBHOOK_TIMEOUT', 30); // seconds

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/php_errors.log');

// Set limits
ini_set('max_execution_time', MAX_EXECUTION_TIME);
ini_set('memory_limit', MEMORY_LIMIT);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create required directories
$dirs = [UPLOAD_PATH, LOGS_PATH];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
