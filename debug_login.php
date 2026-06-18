<?php
/**
 * DNHS Hub - Debug Login
 * 
 * Debug script to check login issues
 * DELETE this file after debugging!
 */

require_once __DIR__ . '/config/config.php';

echo "<h2>DNHS Hub Login Debug</h2>";

// Test database connection
echo "<h3>1. Database Connection</h3>";
try {
    $db = getDBConnection();
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check users table
echo "<h3>2. Users in Database</h3>";
try {
    $stmt = $db->query("SELECT id, username, first_name, last_name, role, is_active, LEFT(password, 30) as password_start FROM users");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p style='color: red;'>✗ No users found in database!</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Role</th><th>Active</th><th>Password Hash (start)</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['first_name']} {$user['last_name']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "<td>{$user['password_start']}...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test password verification
echo "<h3>3. Password Verification Test</h3>";
try {
    $stmt = $db->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        $password = 'admin123';
        $hash = $user['password'];
        
        echo "<p>Testing password: <code>$password</code></p>";
        echo "<p>Stored hash: <code>" . substr($hash, 0, 30) . "...</code></p>";
        
        if (password_verify($password, $hash)) {
            echo "<p style='color: green;'>✓ Password verification SUCCESSFUL</p>";
        } else {
            echo "<p style='color: red;'>✗ Password verification FAILED</p>";
            echo "<p>Generating new hash for comparison:</p>";
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            echo "<p><code>$newHash</code></p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Admin user not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test direct login
echo "<h3>4. Direct Login Test</h3>";
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin' AND is_active = 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        if (password_verify('admin123', $user['password'])) {
            echo "<p style='color: green;'>✓ Login would SUCCEED for admin/admin123</p>";
        } else {
            echo "<p style='color: red;'>✗ Login would FAIL for admin/admin123</p>";
            
            // Fix the password
            echo "<h4>Auto-fixing password...</h4>";
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $fixStmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $fixStmt->execute([$newHash]);
            echo "<p style='color: green;'>✓ Password has been updated. Try logging in now.</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Admin user not found or inactive</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
echo "<p style='color: red;'><strong>IMPORTANT: Delete this file after debugging!</strong></p>";
?>
