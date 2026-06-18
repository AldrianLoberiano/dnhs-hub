<?php
/**
 * DNHS Hub - Archive Student
 * 
 * Archive a student record
 */

require_once __DIR__ . '/../../config/config.php';
requireAuth();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid student ID.');
    redirect(APP_URL . '/students/index.php');
}

// Get student
$stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    setFlashMessage('error', 'Student not found.');
    redirect(APP_URL . '/students/index.php');
}

// Archive student
$stmt = $db->prepare("UPDATE students SET is_archived = 1 WHERE id = ?");
$stmt->execute([$id]);

logAudit('Archive Student', 'Student Records', "Archived student: {$student['first_name']} {$student['last_name']} ({$student['student_number']})");
setFlashMessage('success', 'Student record archived successfully.');
redirect(APP_URL . '/students/index.php');
