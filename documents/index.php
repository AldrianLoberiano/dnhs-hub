<?php
/**
 * DNHS Hub - Student Documents List
 * 
 * Display and manage student documents
 */

$pageTitle = 'Student Documents - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$docType = $_GET['doc_type'] ?? '';
$studentId = intval($_GET['student_id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

// Build query
$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if (!empty($docType)) {
    $where .= " AND sd.document_type_id = ?";
    $params[] = $docType;
}

if ($studentId) {
    $where .= " AND sd.student_id = ?";
    $params[] = $studentId;
}

// Get total count
$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM student_documents sd 
    JOIN students s ON sd.student_id = s.id 
    $where
");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

// Get documents
$stmt = $db->prepare("
    SELECT sd.*, s.student_number, s.first_name, s.last_name, dt.name as doc_type_name 
    FROM student_documents sd 
    JOIN students s ON sd.student_id = s.id 
    JOIN document_types dt ON sd.document_type_id = dt.id 
    $where 
    ORDER BY sd.created_at DESC 
    LIMIT ? OFFSET ?
");
$params[] = $perPage;
$params[] = $pagination['offset'];
$stmt->execute($params);
$documents = $stmt->fetchAll();

// Get document types for filter
$stmt = $db->query("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name");
$docTypes = $stmt->fetchAll();
?>

<div class="page-header">
    <h4><i class="fas fa-folder-open me-2"></i>Student Documents</h4>
    <a href="upload.php" class="btn btn-primary">
        <i class="fas fa-upload me-1"></i>Upload Document
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search by student name or number..." value="<?php echo sanitize($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="doc_type">
                    <option value="">All Document Types</option>
                    <?php foreach ($docTypes as $dt): ?>
                    <option value="<?php echo $dt['id']; ?>" <?php echo $docType == $dt['id'] ? 'selected' : ''; ?>><?php echo sanitize($dt['name']); ?></option>
                    <?php endforeach; ?>
                </select>
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

<!-- Documents Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Document Type</th>
                        <th>File Name</th>
                        <th>Version</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documents)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No documents found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td>
                            <a href="../students/view.php?id=<?php echo $doc['student_id']; ?>">
                                <?php echo sanitize($doc['first_name'] . ' ' . $doc['last_name']); ?>
                            </a>
                            <br><small class="text-muted"><?php echo sanitize($doc['student_number']); ?></small>
                        </td>
                        <td><?php echo sanitize($doc['doc_type_name']); ?></td>
                        <td>
                            <i class="fas fa-file-<?php echo $doc['file_type'] === 'pdf' ? 'pdf text-danger' : 'image text-primary'; ?> me-1"></i>
                            <?php echo sanitize($doc['original_name']); ?>
                        </td>
                        <td>v<?php echo $doc['version']; ?></td>
                        <td><?php echo formatFileSize($doc['file_size']); ?></td>
                        <td><small><?php echo formatDate($doc['created_at']); ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="download.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-primary" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="preview.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-info" title="Preview" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-danger btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        $baseUrl = 'index.php?';
        if (!empty($search)) $baseUrl .= "search=" . urlencode($search) . "&";
        if (!empty($docType)) $baseUrl .= "doc_type=" . urlencode($docType) . "&";
        if ($studentId) $baseUrl .= "student_id=$studentId&";
        echo renderPagination($pagination, $baseUrl);
        ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
