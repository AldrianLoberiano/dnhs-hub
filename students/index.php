<?php
/**
 * DNHS Hub - Student Records List
 * 
 * Display and manage student records
 */

$pageTitle = 'Student Records - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$batch = $_GET['batch'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

// Build query
$where = "WHERE s.is_archived = 0";
$params = [];

if (!empty($search)) {
    $where .= " AND (s.student_number LIKE ? OR s.lrn LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($status)) {
    $where .= " AND s.current_status = ?";
    $params[] = $status;
}

if (!empty($batch)) {
    $where .= " AND s.graduation_batch = ?";
    $params[] = $batch;
}

// Get total count
$stmt = $db->prepare("SELECT COUNT(*) as count FROM students s $where");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

// Get students
$stmt = $db->prepare("
    SELECT s.* 
    FROM students s 
    $where 
    ORDER BY s.created_at DESC 
    LIMIT ? OFFSET ?
");
$params[] = $perPage;
$params[] = $pagination['offset'];
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get batches for filter
$stmt = $db->query("SELECT DISTINCT graduation_batch FROM students WHERE graduation_batch IS NOT NULL AND graduation_batch != '' ORDER BY graduation_batch DESC");
$batches = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="page-header">
    <h4><i class="fas fa-user-graduate me-2"></i>Student Records</h4>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add Student
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search by name, student #, LRN..." value="<?php echo sanitize($search); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="Enrolled" <?php echo $status === 'Enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                    <option value="Graduated" <?php echo $status === 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
                    <option value="Transferred" <?php echo $status === 'Transferred' ? 'selected' : ''; ?>>Transferred</option>
                    <option value="Dropped" <?php echo $status === 'Dropped' ? 'selected' : ''; ?>>Dropped</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="batch">
                    <option value="">All Batches</option>
                    <?php foreach ($batches as $b): ?>
                    <option value="<?php echo sanitize($b); ?>" <?php echo $batch === $b ? 'selected' : ''; ?>><?php echo sanitize($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead>
                    <tr>
                        <th>Student #</th>
                        <th>Name</th>
                        <th>LRN</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Batch</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No students found</td>
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
                        <td><?php echo sanitize($student['gender']); ?></td>
                        <td>
                            <?php
                            $statusClass = [
                                'Enrolled' => 'bg-success',
                                'Graduated' => 'bg-primary',
                                'Transferred' => 'bg-info',
                                'Dropped' => 'bg-danger',
                                'Archived' => 'bg-secondary'
                            ];
                            ?>
                            <span class="badge <?php echo $statusClass[$student['current_status']] ?? 'bg-secondary'; ?>">
                                <?php echo sanitize($student['current_status']); ?>
                            </span>
                        </td>
                        <td><?php echo sanitize($student['graduation_batch'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="view.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="archive.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-danger btn-archive" title="Archive">
                                    <i class="fas fa-archive"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php
        $baseUrl = 'index.php?';
        if (!empty($search)) $baseUrl .= "search=" . urlencode($search) . "&";
        if (!empty($status)) $baseUrl .= "status=" . urlencode($status) . "&";
        if (!empty($batch)) $baseUrl .= "batch=" . urlencode($batch) . "&";
        echo renderPagination($pagination, $baseUrl);
        ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
