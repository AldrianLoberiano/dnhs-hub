<?php
/**
 * DNHS Hub - Add Document Request
 * 
 * Form to create a new document request
 */

require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDBConnection();
$studentId = intval($_GET['student_id'] ?? 0);
$errors = [];

// Get students for dropdown (limited for performance)
$studentSearch = trim($_GET['student_search'] ?? '');
if (!empty($studentSearch)) {
    $stmt = $db->prepare("SELECT id, student_number, first_name, last_name, lrn FROM students WHERE is_archived = 0 AND (student_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR lrn LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?) ORDER BY last_name, first_name LIMIT 50");
    $like = "%$studentSearch%";
    $stmt->execute([$like, $like, $like, $like, $like]);
} else {
    $stmt = $db->query("SELECT id, student_number, first_name, last_name, lrn FROM students WHERE is_archived = 0 ORDER BY last_name, first_name LIMIT 50");
}
$students = $stmt->fetchAll();

// Get document types
$stmt = $db->query("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name");
$docTypes = $stmt->fetchAll();

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
    $studentId = intval($_POST['student_id'] ?? 0);
    $docTypeId = intval($_POST['document_type_id'] ?? 0);
    $purpose = trim($_POST['purpose'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $expectedRelease = $_POST['expected_release_date'] ?? '';
    
    // Validation
    if (!$studentId) $errors[] = 'Please select a student.';
    if (!$docTypeId) $errors[] = 'Please select a document type.';
    
    if (empty($errors)) {
        $trackingNumber = generateTrackingNumber();
        $dateRequested = date('Y-m-d');
        
        $stmt = $db->prepare("
            INSERT INTO document_requests (tracking_number, student_id, document_type_id, purpose, remarks, requested_by, date_requested, expected_release_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt->execute([
            $trackingNumber, $studentId, $docTypeId, $purpose ?: null, 
            $remarks ?: null, $_SESSION['user_id'], $dateRequested, 
            $expectedRelease ?: null
        ]);
        
        $requestId = $db->lastInsertId();
        
        // Add status history
        $stmt = $db->prepare("INSERT INTO request_status_history (request_id, new_status, changed_by, notes) VALUES (?, 'Pending', ?, 'Request created')");
        $stmt->execute([$requestId, $_SESSION['user_id']]);
        
        // Get student info for notification
        $stmt = $db->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();
        
        logAudit('Create Request', 'Document Requests', "Created request $trackingNumber for {$student['first_name']} {$student['last_name']}");
        
        // Create notifications for admin (batch insert)
        $stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
        $stmt->execute();
        $admins = $stmt->fetchAll();
        if (!empty($admins)) {
            $placeholders = [];
            $values = [];
            foreach ($admins as $admin) {
                $placeholders[] = '(?, ?, ?, ?, ?)';
                $values[] = $admin['id'];
                $values[] = 'New Request';
                $values[] = "New document request created: $trackingNumber for {$student['first_name']} {$student['last_name']}";
                $values[] = 'info';
                $values[] = "../requests/view.php?id=$requestId";
            }
            $db->prepare('INSERT INTO notifications (user_id, title, message, type, link) VALUES ' . implode(',', $placeholders))->execute($values);
        }
        
        setFlashMessage('success', "Request created successfully. Tracking Number: $trackingNumber");
        redirect(APP_URL . "/requests/view.php?id=$requestId");
    }
    }
}

$pageTitle = 'New Request - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h4>New Document Request</h4>
        <small style="color: rgba(255,255,255,0.8);">Create a new document request for a student</small>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to List
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle me-2"></i>
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
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2"></i>Request Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student *</label>
                            <input type="text" class="form-control form-control-sm mb-1" id="studentSearch" placeholder="Search by name, #, or LRN..." value="<?php echo sanitize($studentSearch); ?>">
                            <select class="form-select" name="student_id" id="studentSelect" required size="1">
                                <option value="">Select Student</option>
                                <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $studentId == $s['id'] ? 'selected' : ''; ?> 
                                        data-lrn="<?php echo sanitize($s['lrn'] ?? ''); ?>">
                                    <?php echo sanitize($s['last_name'] . ', ' . $s['first_name'] . ' (' . $s['student_number'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Document Type *</label>
                            <select class="form-select" name="document_type_id" required>
                                <option value="">Select Document Type</option>
                                <?php foreach ($docTypes as $dt): ?>
                                <option value="<?php echo $dt['id']; ?>"><?php echo sanitize($dt['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purpose</label>
                            <input type="text" class="form-control" name="purpose" placeholder="e.g., Employment, Transfer, Studies">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expected Release Date</label>
                            <input type="date" class="form-control" name="expected_release_date" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3" placeholder="Additional notes or special instructions..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Create Request
                </button>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Request Information
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Tracking Number:</strong> Will be auto-generated</p>
                <p class="mb-2"><strong>Request Date:</strong> <?php echo date('M d, Y'); ?></p>
                <p class="mb-2"><strong>Requested By:</strong> <?php echo sanitize($_SESSION['full_name']); ?></p>
                <hr>
                <p class="mb-0 text-muted">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        The request will be placed in "Pending" status. You can update the status after creation.
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('studentSearch');
    var debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            var url = new URL(window.location.href);
            url.searchParams.set('student_search', searchInput.value);
            window.location.href = url.toString();
        }, 500);
    });
    if (searchInput.value) {
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
