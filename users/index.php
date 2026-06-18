<?php
/**
 * DNHS Hub - User Management
 * 
 * Admin user management
 */

$pageTitle = 'User Management - DNHS Hub';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$db = getDBConnection();

// Get all users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<div class="page-header">
    <h4><i class="fas fa-users-cog me-2"></i>User Management</h4>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add User
    </a>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover data-table">
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
                        <td><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                        <td>
                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><small><?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y h:i A') : 'Never'; ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="reset_password.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-info" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </a>
                                <a href="toggle_status.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-<?php echo $user['is_active'] ? 'danger' : 'success'; ?>" title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
