<?php
/**
 * DNHS Hub - Archived Students
 * 
 * Display and manage archived student records
 */

$pageTitle = 'Archived Students - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

// Get archived students
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

$search = trim($_GET['search'] ?? '');
$where = "WHERE s.is_archived = 1";
$params = [];

if (!empty($search)) {
    $where .= " AND (s.student_number LIKE ? OR s.lrn LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

$stmt = $db->prepare("SELECT COUNT(*) as count FROM students s $where");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

$stmt = $db->prepare("SELECT s.* FROM students s $where ORDER BY s.updated_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$pagination['offset']);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h4>Archived Students</h4>
        <small style="color: rgba(255,255,255,0.8);">View and restore previously archived student records</small>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Active Students
    </a>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Search archived students..." value="<?php echo sanitize($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="archived.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Archived Students Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead>
                    <tr>
                        <th>Student #</th>
                        <th>Name</th>
                        <th>LRN</th>
                        <th>Status</th>
                        <th>Archived Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No archived students</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><code><?php echo sanitize($student['student_number']); ?></code></td>
                        <td>
                            <strong><?php echo sanitize($student['last_name'] . ', ' . $student['first_name']); ?></strong>
                            <?php if (!empty($student['middle_name'])): ?>
                                <br><small class="text-muted"><?php echo sanitize($student['middle_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo sanitize($student['lrn'] ?? 'N/A'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo sanitize($student['current_status']); ?></span></td>
                        <td><small><?php echo formatDate($student['updated_at']); ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="view.php?id=<?php echo $student['id']; ?>" class="icon-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" action="restore.php" style="display:inline">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                                    <button type="submit" class="icon-btn btn-confirm-restore" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        $baseUrl = 'archived.php?';
        if (!empty($search)) $baseUrl .= "search=" . urlencode($search) . "&";
        echo renderPagination($pagination, $baseUrl);
        ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
