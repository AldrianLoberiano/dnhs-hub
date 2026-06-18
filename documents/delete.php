<?php
/**
 * DNHS Hub - Delete Document
 * 
 * Delete a student document
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

// Delete file from server
$filePath = APP_ROOT . '/' . $doc['file_path'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Delete from database
$stmt = $db->prepare("DELETE FROM student_documents WHERE id = ?");
$stmt->execute([$id]);

logAudit('Delete Document', 'Student Documents', "Deleted document: {$doc['original_name']}");
setFlashMessage('success', 'Document deleted successfully.');

// Redirect back to student documents or documents list
$studentId = $doc['student_id'];
redirect(APP_URL . "/documents/index.php?student_id=$studentId");
