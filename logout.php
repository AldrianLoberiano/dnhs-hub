<?php
/**
 * DNHS Hub - Logout
 * 
 * Handles user logout and session destruction
 */

require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    // Log activity
    logAudit('Logout', 'Authentication', 'User logged out');
    
    // Remove session record
    if (isset($_SESSION['session_token'])) {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->execute([$_SESSION['session_token']]);
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header("Location: login.php");
exit();
