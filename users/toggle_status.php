<?php
/**
 * DNHS Hub - Toggle User Status
 * 
 * Activate/Deactivate a user
 */

require_once __DIR__ . '/../../config/config.php';
requireAdmin();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid user ID.');
    redirect(APP_URL . '/users/index.php');
}

// Prevent self-deactivation
if ($id == $_SESSION['user_id']) {
    setFlashMessage('error', 'You cannot deactivate your own account.');
    redirect(APP_URL . '/users/index.php');
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'User not found.');
    redirect(APP_URL . '/users/index.php');
}

// Toggle status
$newStatus = $user['is_active'] ? 0 : 1;
$action = $newStatus ? 'Activate' : 'Deactivate';

$stmt = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);

logAudit("$action User", 'User Management', "{$action}d user: {$user['username']}");
setFlashMessage('success', "User $action'd successfully.");
redirect(APP_URL . '/users/index.php');
