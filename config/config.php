<?php
/**
 * DNHS Hub - Main Configuration
 * 
 * Application-wide settings and constants
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
define('APP_URL', 'http://localhost/dnhs-hub');
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
