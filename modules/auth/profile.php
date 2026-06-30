<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'User not found.');
    redirect(APP_URL . '/modules/auth/login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $errors[] = 'Current password is required.';
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $errors[] = 'Current password is incorrect.';
            }

            if (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            }
            if (!preg_match('/[A-Z]/', $newPassword)) {
                $errors[] = 'New password must contain at least one uppercase letter.';
            }
            if (!preg_match('/[a-z]/', $newPassword)) {
                $errors[] = 'New password must contain at least one lowercase letter.';
            }
            if (!preg_match('/[0-9]/', $newPassword)) {
                $errors[] = 'New password must contain at least one number.';
            }

            if ($newPassword !== $confirmPassword) {
                $errors[] = 'New passwords do not match.';
            }

            if (empty($errors)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);

                logAudit('Change Password', 'Profile', 'Changed own password');
                setFlashMessage('success', 'Password changed successfully.');
                redirect(APP_URL . '/profile.php');
            }
        }
    }
}

$pageTitle = 'Profile - DNHS Hub';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h4>My Profile</h4>
        <small style="color: rgba(255,255,255,0.8);">View and update your account settings</small>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-id-card me-2"></i>Personal Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Username</strong>
                        <p><?php echo sanitize($user['username']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Role</strong>
                        <p><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>"><?php echo $user['role'] === 'admin' ? 'Administrator' : ucfirst($user['role']); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Full Name</strong>
                        <p><?php echo sanitize(getFullName($user)); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email</strong>
                        <p><?php echo sanitize($user['email'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Last Login</strong>
                        <p><?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y h:i A') : 'Never'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Account Created</strong>
                        <p><?php echo formatDate($user['created_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-key me-2"></i>Change Password
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
