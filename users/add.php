<?php
/**
 * DNHS Hub - Add User
 * 
 * Form to add a new user
 */

$pageTitle = 'Add User - DNHS Hub';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$db = getDBConnection();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'registrar';
    
    // Validation
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';
    if (!empty($email) && !isValidEmail($email)) $errors[] = 'Invalid email address.';
    
    // Check unique username
    if (!empty($username)) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()['count'] > 0) {
            $errors[] = 'Username already exists.';
        }
    }
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            INSERT INTO users (username, password, first_name, middle_name, last_name, email, role)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$username, $hashedPassword, $firstName, $middleName ?: null, $lastName, $email ?: null, $role]);
        
        logAudit('Create User', 'User Management', "Created user: $username");
        setFlashMessage('success', 'User created successfully.');
        redirect(APP_URL . '/users/index.php');
    }
}
?>

<div class="page-header">
    <h4><i class="fas fa-user-plus me-2"></i>Add User</h4>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo sanitize($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Account Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" value="<?php echo sanitize($username ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role *</label>
                            <select class="form-select" name="role" required>
                                <option value="registrar" <?php echo ($role ?? '') === 'registrar' ? 'selected' : ''; ?>>Registrar</option>
                                <option value="admin" <?php echo ($role ?? '') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-id-card me-2"></i>Personal Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="first_name" value="<?php echo sanitize($firstName ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" value="<?php echo sanitize($middleName ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="last_name" value="<?php echo sanitize($lastName ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?php echo sanitize($email ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Create User
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
