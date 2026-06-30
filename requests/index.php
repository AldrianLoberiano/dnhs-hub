<?php
/**
 * DNHS Hub - Document Requests List
 * 
 * Display and manage document requests
 */

$pageTitle = 'Document Requests - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$docType = $_GET['doc_type'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

// Build query
$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (dr.tracking_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_number LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($status)) {
    $where .= " AND dr.status = ?";
    $params[] = $status;
}

if (!empty($docType)) {
    $where .= " AND dr.document_type_id = ?";
    $params[] = $docType;
}

// Get total count
$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM document_requests dr 
    JOIN students s ON dr.student_id = s.id 
    $where
");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

// Get requests
$stmt = $db->prepare("
    SELECT dr.*, s.first_name, s.last_name, s.student_number, dt.name as doc_type_name,
           CONCAT(u.first_name, ' ', u.last_name) as registrar_name
    FROM document_requests dr 
    JOIN students s ON dr.student_id = s.id 
    JOIN document_types dt ON dr.document_type_id = dt.id 
    LEFT JOIN users u ON dr.requested_by = u.id 
    $where 
    ORDER BY dr.created_at DESC 
    LIMIT " . (int)$perPage . " OFFSET " . (int)$pagination['offset']
);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get document types for filter
$stmt = $db->query("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name");
$docTypes = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h4>Document Requests</h4>
        <small style="color: rgba(255,255,255,0.8);">Track and process all document requests</small>
    </div>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>New Request
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" id="filterSearch" placeholder="Search tracking #, student..." value="<?php echo sanitize($search); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Processing" <?php echo $status === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="Ready for Release" <?php echo $status === 'Ready for Release' ? 'selected' : ''; ?>>Ready for Release</option>
                    <option value="Released" <?php echo $status === 'Released' ? 'selected' : ''; ?>>Released</option>
                    <option value="Rejected" <?php echo $status === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="Cancelled" <?php echo $status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="doc_type" id="filterDocType">
                    <option value="">All Document Types</option>
                    <?php foreach ($docTypes as $dt): ?>
                    <option value="<?php echo $dt['id']; ?>" <?php echo $docType == $dt['id'] ? 'selected' : ''; ?>><?php echo sanitize($dt['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var debounceTimer;
    var form = document.getElementById('filterForm');
    document.getElementById('filterStatus').addEventListener('change', function() { form.submit(); });
    document.getElementById('filterDocType').addEventListener('change', function() { form.submit(); });
    document.getElementById('filterSearch').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { form.submit(); }, 500);
    });
});
</script>

<!-- Requests Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Student</th>
                        <th>Document Type</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Date Requested</th>
                        <th>Registrar</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No requests found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><code><?php echo sanitize($req['tracking_number']); ?></code></td>
                        <td>
                            <a href="../students/view.php?id=<?php echo $req['student_id']; ?>">
                                <?php echo sanitize($req['last_name'] . ', ' . $req['first_name']); ?>
                            </a>
                            <br><small class="text-muted"><?php echo sanitize($req['student_number']); ?></small>
                        </td>
                        <td><?php echo sanitize($req['doc_type_name']); ?></td>
                        <td><small><?php echo sanitize($req['purpose'] ?? 'N/A'); ?></small></td>
                        <td><span class="badge <?php echo getStatusBadgeClass($req['status']); ?>"><?php echo sanitize($req['status']); ?></span></td>
                        <td><small><?php echo formatDate($req['date_requested']); ?></small></td>
                        <td><small><?php echo sanitize($req['registrar_name'] ?? 'N/A'); ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="view.php?id=<?php echo $req['id']; ?>" class="icon-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../claims/stub.php?id=<?php echo $req['id']; ?>" class="icon-btn" title="Claim Stub" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-end mt-3">
    <?php
    $baseUrl = 'index.php?';
    if (!empty($search)) $baseUrl .= "search=" . urlencode($search) . "&";
    if (!empty($status)) $baseUrl .= "status=" . urlencode($status) . "&";
    if (!empty($docType)) $baseUrl .= "doc_type=" . urlencode($docType) . "&";
    echo renderPagination($pagination, $baseUrl);
    ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
