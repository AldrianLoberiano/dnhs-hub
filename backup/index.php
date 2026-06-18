<?php
/**
 * DNHS Hub - Backup & Restore
 * 
 * Database backup and restore functionality
 */

$pageTitle = 'Backup & Restore - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$db = getDBConnection();

// Create backups directory if not exists
if (!is_dir(BACKUPS_PATH)) {
    mkdir(BACKUPS_PATH, 0755, true);
}

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid security token.');
        redirect(APP_URL . '/backup/index.php');
    }
    if ($_POST['action'] === 'backup') {
        $filename = 'dnhs_hub_backup_' . date('Y-m-d_His') . '.sql';
        $filePath = BACKUPS_PATH . '/' . $filename;
        
        // Get all tables
        $tables = [];
        $result = $db->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $sql = "-- DNHS Hub Database Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "CREATE DATABASE IF NOT EXISTS dnhs_hub;\nUSE dnhs_hub;\n\n";
        
        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $result = $db->query("SHOW CREATE TABLE $table");
            $row = $result->fetch(PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS `$table`;\n{$row[1]};\n\n";
            
            // Get data
            $result = $db->query("SELECT * FROM $table");
            $rowCount = $result->rowCount();
            
            if ($rowCount > 0) {
                $sql .= "LOCK TABLES `$table` WRITE;\n";
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $values = array_map(function($val) use ($db) {
                        return $val === null ? 'NULL' : $db->quote($val);
                    }, $row);
                    $sql .= "INSERT INTO `$table` VALUES(" . implode(', ', $values) . ");\n";
                }
                $sql .= "UNLOCK TABLES;\n\n";
            }
        }
        
        if (file_put_contents($filePath, $sql)) {
            $fileSize = filesize($filePath);
            
            // Log backup
            $stmt = $db->prepare("INSERT INTO backups (filename, file_size, created_by, notes) VALUES (?, ?, ?, ?)");
            $stmt->execute([$filename, $fileSize, $_SESSION['user_id'], 'Manual backup']);
            
            logAudit('Backup Database', 'Backup', "Created backup: $filename");
            setFlashMessage('success', 'Database backup created successfully.');
        } else {
            setFlashMessage('error', 'Failed to create backup file.');
        }
        
        redirect(APP_URL . '/backup/index.php');
    }
    
    if ($_POST['action'] === 'restore' && isset($_FILES['backup_file'])) {
        $file = $_FILES['backup_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'Error uploading backup file.');
            redirect(APP_URL . '/backup/index.php');
        }
        
        $content = file_get_contents($file['tmp_name']);
        
        if (empty($content)) {
            setFlashMessage('error', 'Backup file is empty.');
            redirect(APP_URL . '/backup/index.php');
        }
        
        // Execute SQL
        try {
            $db->exec($content);
            logAudit('Restore Database', 'Backup', 'Database restored from backup');
            setFlashMessage('success', 'Database restored successfully.');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error restoring database: ' . $e->getMessage());
        }
        
        redirect(APP_URL . '/backup/index.php');
    }
}

// Get backup history
$stmt = $db->prepare("
    SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) as created_by_name 
    FROM backups b 
    LEFT JOIN users u ON b.created_by = u.id 
    ORDER BY b.created_at DESC 
    LIMIT 20
");
$stmt->execute([]);
$backups = $stmt->fetchAll();
?>

<div class="page-header">
    <h4><i class="fas fa-database me-2"></i>Backup & Restore</h4>
</div>

<div class="row">
    <!-- Backup -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-download me-2"></i>Create Backup
            </div>
            <div class="card-body">
                <p class="text-muted">Create a backup of the entire database. The backup file will be saved to the server and can be downloaded.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="backup">
                    <?php generateCSRFToken(); ?>
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-database me-1"></i>Create Backup Now
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Restore -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-upload me-2"></i>Restore Database
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will overwrite the current database. Make sure to create a backup first.
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="restore">
                    <?php generateCSRFToken(); ?>
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Select Backup File (.sql)</label>
                        <input type="file" class="form-control" name="backup_file" accept=".sql" required>
                    </div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to restore? This will overwrite all current data.');">
                        <i class="fas fa-undo me-1"></i>Restore Database
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Backup History -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>Backup History
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Size</th>
                        <th>Created By</th>
                        <th>Notes</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($backups)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No backups yet</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><code><?php echo sanitize($backup['filename']); ?></code></td>
                        <td><?php echo formatFileSize($backup['file_size']); ?></td>
                        <td><?php echo sanitize($backup['created_by_name'] ?? 'System'); ?></td>
                        <td><?php echo sanitize($backup['notes'] ?? ''); ?></td>
                        <td><small><?php echo formatDate($backup['created_at'], 'M d, Y h:i A'); ?></small></td>
                        <td>
                            <a href="download.php?id=<?php echo $backup['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
