<?php
/**
 * DNHS Hub - Edit User
 * 
 * Form to edit an existing user
 */

require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid user ID.');
    redirect(APP_URL . '/users/index.php');
}

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'User not found.');
    redirect(APP_URL . '/users/index.php');
}

$pageTitle = 'Edit User - DNHS Hub';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'registrar';
    
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';
    
    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$firstName, $middleName ?: null, $lastName, $email ?: null, $role, $id]);
        
        logAudit('Update User', 'User Management', "Updated user: {$user['username']}");
        setFlashMessage('success', 'User updated successfully.');
        redirect(APP_URL . '/users/index.php');
    }
    }
    
    $user = array_merge($user, $_POST);
}
?>

<div class="page-header">
    <div>
        <h4>Edit User</h4>
        <small style="color: rgba(255,255,255,0.8);">Update user account information</small>
    </div>
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
                <?php generateCSRFToken(); ?>
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>User Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo sanitize($user['username']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role *</label>
                            <select class="form-select" name="role" required>
                                <option value="registrar" <?php echo $user['role'] === 'registrar' ? 'selected' : ''; ?>>Registrar</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="first_name" value="<?php echo sanitize($user['first_name']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name" value="<?php echo sanitize($user['middle_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="last_name" value="<?php echo sanitize($user['last_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?php echo sanitize($user['email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update User
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
