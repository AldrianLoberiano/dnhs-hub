<?php
/**
 * DNHS Hub - Mark Notification Read
 * 
 * Mark a notification as read (AJAX endpoint)
 */

require_once __DIR__ . '/../../config/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id) {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

header('HTTP/1.1 405 Method Not Allowed');
