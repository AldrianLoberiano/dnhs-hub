<?php
/**
 * DNHS Hub - User Management
 * 
 * Admin user management
 */

$pageTitle = 'User Management - DNHS Hub';
require_once __DIR__ . '/../config/config.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

// Get all users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h4>User Management</h4>
        <small style="color: rgba(255,255,255,0.8);">Manage system users and their roles</small>
    </div>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add User
    </a>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><code><?php echo sanitize($user['username']); ?></code></td>
                        <td><?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo sanitize($user['email'] ?? 'N/A'); ?></td>
                        <td><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>"><?php echo $user['role'] === 'admin' ? 'Administrator' : ucfirst($user['role']); ?></span></td>
                        <td>
                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><small><?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y h:i A') : 'Never'; ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="icon-btn" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="reset_password.php?id=<?php echo $user['id']; ?>" class="icon-btn" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </a>
                                <form method="POST" action="toggle_status.php" style="display:inline">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                                    <button type="submit" class="icon-btn btn-confirm-toggle" data-action="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>" title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
