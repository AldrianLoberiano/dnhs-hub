<?php
/**
 * DNHS Hub - Fix Default Passwords
 * 
 * Run this script once to fix the default user passwords
 * Access: http://localhost/dnhs-hub/fix_passwords.php
 * DELETE this file after running!
 */

require_once __DIR__ . '/config/config.php';

$db = getDBConnection();

// Hash for "admin123" and "registrar123"
$passwordHash = password_hash('admin123', PASSWORD_DEFAULT);

// Update admin password
$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$passwordHash]);
$adminUpdated = $stmt->rowCount();

// Update registrar password
$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'registrar'");
$stmt->execute([$passwordHash]);
$registrarUpdated = $stmt->rowCount();

echo "<h2>Password Fix Results</h2>";
echo "<p>Admin password updated: " . ($adminUpdated ? "YES" : "NO (user not found or already correct)") . "</p>";
echo "<p>Registrar password updated: " . ($registrarUpdated ? "YES" : "NO (user not found or already correct)") . "</p>";
echo "<br>";
echo "<p><strong>New credentials:</strong></p>";
echo "<ul>";
echo "<li>Username: <code>admin</code> | Password: <code>admin123</code></li>";
echo "<li>Username: <code>registrar</code> | Password: <code>admin123</code></li>";
echo "</ul>";
echo "<br>";
echo "<p style='color: red;'><strong>IMPORTANT: Delete this file after running!</strong></p>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>
