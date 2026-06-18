<?php
/**
 * DNHS Hub - Fix Document Paths
 * 
 * Run this script once to fix file_path in database
 * Access: http://localhost/dnhs-hub/fix_paths.php
 * DELETE this file after running!
 */

require_once __DIR__ . '/config/config.php';

$db = getDBConnection();

echo "<h2>Fixing Document File Paths</h2>";

// Update paths that start with 'uploads/' to 'assets/uploads/'
$stmt = $db->prepare("UPDATE student_documents SET file_path = CONCAT('assets/', file_path) WHERE file_path LIKE 'uploads/%' AND file_path NOT LIKE 'assets/%'");
$stmt->execute();
$count = $stmt->rowCount();

echo "<p style='color: green;'>✓ Fixed $count document file paths</p>";

// Also verify files exist
$stmt = $db->query("SELECT id, file_path, original_name FROM student_documents");
$docs = $stmt->fetchAll();

$missing = 0;
foreach ($docs as $doc) {
    $fullPath = APP_ROOT . '/' . $doc['file_path'];
    if (!file_exists($fullPath)) {
        echo "<p style='color: red;'>✗ File not found: {$doc['original_name']} ({$doc['file_path']})</p>";
        $missing++;
    }
}

if ($missing === 0) {
    echo "<p style='color: green;'>✓ All document files exist on server</p>";
}

echo "<br>";
echo "<p style='color: red;'><strong>IMPORTANT: Delete this file after running!</strong></p>";
echo "<p><a href='documents/index.php'>Go to Documents</a></p>";
?>
