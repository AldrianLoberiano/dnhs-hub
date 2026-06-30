<?php
/**
 * DNHS Hub - Main Configuration
 * 
 * Application-wide settings and constants
 */

// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default page title
if (!isset($pageTitle)) $pageTitle = 'DNHS Hub';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

// Application paths
define('APP_ROOT', dirname(__DIR__));
define('ASSETS_PATH', APP_ROOT . '/assets');
define('UPLOADS_PATH', ASSETS_PATH . '/uploads');
define('DOCUMENTS_PATH', UPLOADS_PATH . '/documents');
define('PROFILES_PATH', UPLOADS_PATH . '/profiles');
define('BACKUPS_PATH', APP_ROOT . '/backups');

// Application URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('APP_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/dnhs-hub');
define('ASSETS_URL', APP_URL . '/assets');

// File upload settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Include database configuration
require_once __DIR__ . '/database.php';

// Include helper functions
require_once __DIR__ . '/../helpers/functions.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
