<?php
/**
 * DNHS Hub - Audit Logs
 * 
 * Display system audit trail
 */

$pageTitle = 'Audit Logs - DNHS Hub';
require_once __DIR__ . '/../config/config.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

// Get filter parameters
$action = $_GET['action'] ?? '';
$module = $_GET['module'] ?? '';
$startDate = isset($_GET['start_date']) ? date('Y-m-d', strtotime($_GET['start_date'])) : '';
$endDate = isset($_GET['end_date']) ? date('Y-m-d', strtotime($_GET['end_date'])) : '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

// Build query
$where = "WHERE 1=1";
$params = [];

if (!empty($action)) {
    $where .= " AND al.action LIKE ?";
    $params[] = "%$action%";
}

if (!empty($module)) {
    $where .= " AND al.module = ?";
    $params[] = $module;
}

if (!empty($startDate)) {
    $where .= " AND al.created_at >= ?";
    $params[] = $startDate . ' 00:00:00';
}

if (!empty($endDate)) {
    $where .= " AND al.created_at <= ?";
    $params[] = $endDate . ' 23:59:59';
}

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM audit_logs al $where");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

// Get logs
$stmt = $db->prepare("
    SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name 
    FROM audit_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    $where 
    ORDER BY al.created_at DESC 
    LIMIT " . (int)$perPage . " OFFSET " . (int)$pagination['offset']
);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get unique modules for filter
$stmt = $db->query("SELECT DISTINCT module FROM audit_logs ORDER BY module");
$modules = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="page-header">
    <div>
        <h4>Audit Logs</h4>
        <small style="color: rgba(255,255,255,0.8);">View all system activity and changes</small>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <input type="text" class="form-control" name="action" placeholder="Action..." value="<?php echo sanitize($action); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="module">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $m): ?>
                    <option value="<?php echo sanitize($m); ?>" <?php echo $module === $m ? 'selected' : ''; ?>><?php echo sanitize($m); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" placeholder="Start Date">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" placeholder="End Date">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No audit logs found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><small><?php echo formatDate($log['created_at'], 'M d, Y h:i:s A'); ?></small></td>
                        <td><?php echo sanitize($log['user_name'] ?? 'System'); ?></td>
                        <td><span class="badge bg-primary"><?php echo sanitize($log['action']); ?></span></td>
                        <td><?php echo sanitize($log['module']); ?></td>
                        <td><small><?php echo sanitize($log['description'] ?? ''); ?></small></td>
                        <td><small><?php echo sanitize($log['ip_address'] ?? ''); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        $baseUrl = 'index.php?';
        if (!empty($action)) $baseUrl .= "action=" . urlencode($action) . "&";
        if (!empty($module)) $baseUrl .= "module=" . urlencode($module) . "&";
        if (!empty($startDate)) $baseUrl .= "start_date=" . urlencode($startDate) . "&";
        if (!empty($endDate)) $baseUrl .= "end_date=" . urlencode($endDate) . "&";
        echo renderPagination($pagination, $baseUrl);
        ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
