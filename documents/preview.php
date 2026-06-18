<?php
/**
 * DNHS Hub - Preview Document
 * 
 * Preview a student document
 */

require_once __DIR__ . '/../../config/config.php';
requireAuth();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid document ID.');
    redirect(APP_URL . '/documents/index.php');
}

// Get document
$stmt = $db->prepare("SELECT * FROM student_documents WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    setFlashMessage('error', 'Document not found.');
    redirect(APP_URL . '/documents/index.php');
}

$filePath = APP_ROOT . '/' . $doc['file_path'];

if (!file_exists($filePath)) {
    setFlashMessage('error', 'Document file not found on server.');
    redirect(APP_URL . '/documents/index.php');
}

// Determine content type
$contentTypes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png'
];

$contentType = $contentTypes[$doc['file_type']] ?? 'application/octet-stream';

// Send file for preview
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . $doc['original_name'] . '"');

readfile($filePath);
exit();
