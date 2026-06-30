<?php
/**
 * DNHS Hub - Delete Document
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid request.']); exit; }
    setFlashMessage('error', 'Invalid request method.');
    redirect(APP_URL . '/documents/index.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid security token.']); exit; }
    setFlashMessage('error', 'Invalid security token.');
    redirect(APP_URL . '/documents/index.php');
}

$db = getDBConnection();
$id = intval($_POST['id'] ?? 0);

if (!$id) {
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Invalid document ID.']); exit; }
    setFlashMessage('error', 'Invalid document ID.');
    redirect(APP_URL . '/documents/index.php');
}

$stmt = $db->prepare("SELECT * FROM student_documents WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Document not found.']); exit; }
    setFlashMessage('error', 'Document not found.');
    redirect(APP_URL . '/documents/index.php');
}

$filePath = realpath(APP_ROOT . '/' . $doc['file_path']);
$allowedDir = realpath(DOCUMENTS_PATH);
if ($filePath !== false && $allowedDir !== false && strpos($filePath, $allowedDir) === 0 && file_exists($filePath)) {
    unlink($filePath);
}

$stmt = $db->prepare("DELETE FROM student_documents WHERE id = ?");
$stmt->execute([$id]);

logAudit('Delete Document', 'Student Documents', "Deleted document: {$doc['original_name']}");

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Document deleted.']);
    exit;
}

setFlashMessage('success', 'Document deleted successfully.');
$studentId = $doc['student_id'];
redirect(APP_URL . "/documents/index.php?student_id=$studentId");
