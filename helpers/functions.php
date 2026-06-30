<?php
/**
 * DNHS Hub - Helper Functions
 * 
 * Common utility functions used throughout the application
 */

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is registrar
 * 
 * @return bool
 */
function isRegistrar() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'registrar';
}

/**
 * Redirect to specified URL
 * 
 * @param string $url Target URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Sanitize input data
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Regenerate CSRF token (call after successful form submission)
 */
function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Get CSRF token
 * 
 * @return string CSRF token
 */
function getCSRFToken() {
    generateCSRFToken();
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool
 */
function validateCSRFToken($token) {
    $valid = isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    if ($valid) {
        regenerateCSRFToken();
    }
    return $valid;
}

/**
 * Set flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message array
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $type = $message['type'];
        $text = $message['message'];
        $alertClass = $type === 'error' ? 'alert-danger' : "alert-$type";
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
        echo sanitize($text);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
    }
}

/**
 * Generate unique tracking number
 * 
 * @return string Tracking number
 */
function generateTrackingNumber() {
    $year = date('Y');
    $db = getDBConnection();
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM document_requests WHERE date_requested >= ? AND date_requested < ?");
    $stmt->execute([$year . '-01-01', ($year + 1) . '-01-01']);
    $result = $stmt->fetch();
    $count = $result['count'] + 1;
    
    return sprintf("DNHS-%s-%06d", $year, $count);
}

/**
 * Generate unique student number
 * 
 * @return string Student number
 */
function generateStudentNumber() {
    $year = date('Y');
    $db = getDBConnection();
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE created_at >= ? AND created_at < ?");
    $stmt->execute([$year . '-01-01', ($year + 1) . '-01-01']);
    $result = $stmt->fetch();
    $count = $result['count'] + 1;
    
    return sprintf("DNHS-%s-%04d", $year, $count);
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Get user's full name
 * 
 * @param array $user User data
 * @return string Full name
 */
function getFullName($user) {
    $name = $user['first_name'];
    if (!empty($user['middle_name'])) {
        $name .= ' ' . $user['middle_name'];
    }
    $name .= ' ' . $user['last_name'];
    if (!empty($user['suffix'])) {
        $name .= ' ' . $user['suffix'];
    }
    return $name;
}

/**
 * Log audit activity
 * 
 * @param string $action Action performed
 * @param string $module Module affected
 * @param string $description Description
 */
function logAudit($action, $module, $description = '') {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, module, description, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $module, $description, $ipAddress]);
}

/**
 * Create notification
 * 
 * @param int|null $userId Target user ID (null for all admins)
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type
 * @param string|null $link Optional link
 */
function createNotification($userId, $title, $message, $type = 'info', $link = null) {
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $message, $type, $link]);
}

/**
 * Get unread notification count
 * 
 * @param int $userId User ID
 * @return int Count
 */
function getUnreadNotificationCount($userId) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['count'];
}

/**
 * Check file extension
 * 
 * @param string $filename Filename
 * @return bool
 */
function isAllowedFile($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_FILE_TYPES);
}

/**
 * Get file extension
 * 
 * @param string $filename Filename
 * @return string Extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format file size
 * 
 * @param int $bytes Size in bytes
 * @return string Formatted size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Get pagination data
 * 
 * @param int $total Total records
 * @param int $perPage Records per page
 * @param int $currentPage Current page
 * @return array Pagination data
 */
function getPagination($total, $perPage = 10, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}

/**
 * Render pagination HTML
 * 
 * @param array $pagination Pagination data
 * @param string $baseUrl Base URL
 * @return string Pagination HTML
 */
function renderPagination($pagination, $baseUrl = '?') {
    if ($pagination['total_pages'] <= 1) return '';
    
    $totalPages = $pagination['total_pages'];
    $currentPage = $pagination['current_page'];
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage - 1) . '">&laquo;</a></li>';
    }
    
    // Smart page window
    $range = 2;
    $pages = [];
    
    // Always include first page
    $pages[] = 1;
    
    // Pages around current
    for ($i = max(2, $currentPage - $range); $i <= min($totalPages - 1, $currentPage + $range); $i++) {
        $pages[] = $i;
    }
    
    // Always include last page
    if ($totalPages > 1) {
        $pages[] = $totalPages;
    }
    
    // Remove duplicates and sort
    $pages = array_unique($pages);
    sort($pages);
    
    $prevPage = 0;
    foreach ($pages as $i) {
        // Add ellipsis if there's a gap
        if ($i - $prevPage > 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $active = $i === $currentPage ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a></li>';
        $prevPage = $i;
    }
    
    // Next
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . ($currentPage + 1) . '">&raquo;</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Philippine format)
 * 
 * @param string $phone Phone number
 * @return bool
 */
function isValidPhone($phone) {
    return preg_match('/^(09|\+639)\d{9}$/', $phone) === 1;
}

/**
 * Get status badge class
 * 
 * @param string $status Status string
 * @return string Bootstrap badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'Pending' => 'bg-warning text-dark',
        'Approved' => 'bg-info',
        'Processing' => 'bg-primary',
        'Ready for Release' => 'bg-success',
        'Released' => 'bg-secondary',
        'Rejected' => 'bg-danger',
        'Cancelled' => 'bg-dark'
    ];
    return $classes[$status] ?? 'bg-secondary';
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page.');
        redirect(APP_URL . '/login.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect(APP_URL . '/dashboard.php');
    }
}

/**
 * Escape output for HTML
 * 
 * @param string $data Data to escape
 * @return string Escaped data
 */
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}
