<?php
/**
 * DNHS Hub - Restore Student
 * 
 * Restore an archived student record
 */

require_once __DIR__ . '/../config/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    redirect(APP_URL . '/students/archived.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid security token.');
    redirect(APP_URL . '/students/archived.php');
}

$db = getDBConnection();
$id = intval($_POST['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid student ID.');
    redirect(APP_URL . '/students/archived.php');
}

// Get student
$stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    setFlashMessage('error', 'Student not found.');
    redirect(APP_URL . '/students/archived.php');
}

// Restore student
$stmt = $db->prepare("UPDATE students SET is_archived = 0 WHERE id = ?");
$stmt->execute([$id]);

logAudit('Restore Student', 'Student Records', "Restored student: {$student['first_name']} {$student['last_name']} ({$student['student_number']})");
setFlashMessage('success', 'Student record restored successfully.');
redirect(APP_URL . '/students/index.php');
