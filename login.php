<?php
/**
 * DNHS Hub - Login Page
 * 
 * Authentication page for administrators and registrars
 */

require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$db = getDBConnection();
$error = '';
$locked = false;
$lockoutTime = 0;

// Rate limiting constants
define('MAX_ATTEMPTS', 5);
define('LOCKOUT_MINUTES', 15);
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Check if IP is locked out
$stmt = $db->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
$stmt->execute([$ipAddress, LOCKOUT_MINUTES]);
$failedAttempts = $stmt->fetch()['attempts'];

if ($failedAttempts >= MAX_ATTEMPTS) {
    $locked = true;
    $stmt = $db->prepare("SELECT MIN(attempted_at) as first_attempt FROM login_attempts WHERE ip_address = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->execute([$ipAddress, LOCKOUT_MINUTES]);
    $firstAttempt = $stmt->fetch()['first_attempt'];
    if ($firstAttempt) {
        $lockoutTime = strtotime($firstAttempt) + (LOCKOUT_MINUTES * 60) - time();
    }
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } elseif ($locked) {
        $error = 'Too many failed attempts. Please try again in ' . ceil($lockoutTime / 60) . ' minutes.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Record successful login
                $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, username, success) VALUES (?, ?, 1)");
                $stmt->execute([$ipAddress, $username]);
                
                // Clear failed attempts for this IP
                $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ? AND success = 0");
                $stmt->execute([$ipAddress]);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = getFullName($user);
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Update last login
                $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Log activity
                logAudit('Login', 'Authentication', 'User logged in successfully');
                
                // Create session record
                $token = bin2hex(random_bytes(32));
                $stmt = $db->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, last_activity) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$user['id'], $token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
                $_SESSION['session_token'] = $token;
                
                redirect(APP_URL . '/dashboard.php');
            } else {
                // Record failed attempt
                $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, username, success) VALUES (?, ?, 0)");
                $stmt->execute([$ipAddress, $username]);
                
                $remaining = MAX_ATTEMPTS - ($failedAttempts + 1);
                if ($remaining <= 0) {
                    $error = 'Too many failed attempts. Account locked for ' . LOCKOUT_MINUTES . ' minutes.';
                } else {
                    $error = 'Invalid username or password. ' . $remaining . ' attempt(s) remaining before lockout.';
                }
                logAudit('Failed Login', 'Authentication', "Failed login attempt for username: $username from IP: $ipAddress");
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="assets/images/school-logo.png">
    <title>Login - DNHS Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-left">
        <div class="login-left-content">
        </div>
    </div>
    
    <div class="login-right">
        <div class="login-form-wrapper">
            <div class="login-header">
                <div class="school-logo">
                    <img src="assets/images/school-logo.png" alt="School Logo">
                </div>
                <h1 class="system-name">DNHS Hub</h1>
                <p class="system-subtitle">Sign in to your account</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <?php generateCSRFToken(); ?>
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Enter your username"
                               value="<?php echo sanitize($username ?? ''); ?>" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your password" required>
                        <button class="btn-toggle-password" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-arrow-right-to-bracket me-2"></i>Sign In
                </button>
            </form>
            
            <div class="login-footer">
                <p><i class="fas fa-shield-halved"></i>Authorized Personnel Only</p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
