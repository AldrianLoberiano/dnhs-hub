<?php
/**
 * DNHS Hub - Download Backup
 * 
 * Download a backup file
 */

require_once __DIR__ . '/../config/config.php';
requireAdmin();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid backup ID.');
    redirect(APP_URL . '/backup/index.php');
}

$stmt = $db->prepare("SELECT * FROM backups WHERE id = ?");
$stmt->execute([$id]);
$backup = $stmt->fetch();

if (!$backup) {
    setFlashMessage('error', 'Backup not found.');
    redirect(APP_URL . '/backup/index.php');
}

$filePath = BACKUPS_PATH . '/' . $backup['filename'];

if (!file_exists($filePath)) {
    setFlashMessage('error', 'Backup file not found on server.');
    redirect(APP_URL . '/backup/index.php');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $backup['filename']) . '"');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit();
