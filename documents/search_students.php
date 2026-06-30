<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

header('Content-Type: application/json');
$db = getDBConnection();

$q = trim($_GET['q'] ?? '');
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare("SELECT id, student_number, first_name, last_name FROM students WHERE id = ? AND is_archived = 0");
    $stmt->execute([$id]);
} elseif (strlen($q) >= 2) {
    $like = "%$q%";
    $stmt = $db->prepare("SELECT id, student_number, first_name, last_name FROM students WHERE is_archived = 0 AND (student_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR lrn LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?) ORDER BY last_name, first_name LIMIT 20");
    $stmt->execute([$like, $like, $like, $like, $like]);
} else {
    echo json_encode([]);
    exit;
}

echo json_encode($stmt->fetchAll());
