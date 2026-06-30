<?php
/**
 * DNHS Hub - Download Document
 * 
 * Download a student document
 */

require_once __DIR__ . '/../../config/config.php';
requireAuth();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid document ID.');
    redirect(APP_URL . '/modules/index.php');
}

// Get document
$stmt = $db->prepare("SELECT * FROM student_documents WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    setFlashMessage('error', 'Document not found.');
    redirect(APP_URL . '/modules/index.php');
}

$filePath = realpath(APP_ROOT . '/' . $doc['file_path']);
$allowedDir = realpath(DOCUMENTS_PATH);

if ($filePath === false || $allowedDir === false || strpos($filePath, $allowedDir) !== 0) {
    setFlashMessage('error', 'Invalid file path.');
    redirect(APP_URL . '/modules/index.php');
}

if (!file_exists($filePath)) {
    setFlashMessage('error', 'Document file not found on server.');
    redirect(APP_URL . '/modules/index.php');
}

// Log download
logAudit('Download Document', 'Student Documents', "Downloaded document: {$doc['original_name']}");

// Send file
$safeFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $doc['original_name']);
$safeFilename = str_replace('"', '_', $safeFilename);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');

readfile($filePath);
exit();
