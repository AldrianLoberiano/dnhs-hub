<?php
/**
 * DNHS Hub - View Document Request
 * 
 * Display document request details and allow status updates
 */

require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid request ID.');
    redirect(APP_URL . '/requests/index.php');
}

// Get request
$stmt = $db->prepare("
    SELECT dr.*, s.first_name, s.last_name, s.student_number, s.lrn, s.contact_number, s.email,
           dt.name as doc_type_name,
           CONCAT(u.first_name, ' ', u.last_name) as registrar_name
    FROM document_requests dr 
    JOIN students s ON dr.student_id = s.id 
    JOIN document_types dt ON dr.document_type_id = dt.id 
    LEFT JOIN users u ON dr.requested_by = u.id 
    WHERE dr.id = ?
");
$stmt->execute([$id]);
$request = $stmt->fetch();

if (!$request) {
    setFlashMessage('error', 'Request not found.');
    redirect(APP_URL . '/requests/index.php');
}

$pageTitle = "Request {$request['tracking_number']} - DNHS Hub";

// Get status history
$stmt = $db->prepare("
    SELECT rsh.*, CONCAT(u.first_name, ' ', u.last_name) as changed_by_name 
    FROM request_status_history rsh 
    LEFT JOIN users u ON rsh.changed_by = u.id 
    WHERE rsh.request_id = ? 
    ORDER BY rsh.created_at DESC
");
$stmt->execute([$id]);
$statusHistory = $stmt->fetchAll();
?>

<div class="page-header">
    <h4>Request: <?php echo sanitize($request['tracking_number']); ?>
        <span class="badge <?php echo getStatusBadgeClass($request['status']); ?> ms-2"><?php echo sanitize($request['status']); ?></span>
    </h4>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        <a href="../claims/stub.php?id=<?php echo $id; ?>" class="btn btn-success" target="_blank">
            <i class="fas fa-print me-1"></i>Print Claim Stub
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Request Details -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Request Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>Tracking Number</strong>
                        <p class="mb-0"><code><?php echo sanitize($request['tracking_number']); ?></code></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Date Requested</strong>
                        <p class="mb-0"><?php echo formatDate($request['date_requested']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Expected Release</strong>
                        <p class="mb-0"><?php echo !empty($request['expected_release_date']) ? formatDate($request['expected_release_date']) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Document Type</strong>
                        <p class="mb-0"><?php echo sanitize($request['doc_type_name']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Purpose</strong>
                        <p class="mb-0"><?php echo sanitize($request['purpose'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Requested By</strong>
                        <p class="mb-0"><?php echo sanitize($request['registrar_name'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Remarks</strong>
                        <p class="mb-0"><?php echo nl2br(sanitize($request['remarks'] ?? 'N/A')); ?></p>
                    </div>
                    <?php if (!empty($request['actual_release_date'])): ?>
                    <div class="col-md-3">
                        <strong>Released Date</strong>
                        <p class="mb-0"><?php echo formatDate($request['actual_release_date']); ?></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Released To</strong>
                        <p class="mb-0"><?php echo sanitize($request['released_to'] ?? 'N/A'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Status History -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Status History
            </div>
            <div class="card-body">
                <?php if (empty($statusHistory)): ?>
                <p class="text-muted text-center mb-0">No status history</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($statusHistory as $history): ?>
                    <div class="timeline-item mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>
                                <?php echo sanitize($history['old_status'] ?? 'Created'); ?> 
                                <i class="fas fa-arrow-right mx-2"></i> 
                                <?php echo sanitize($history['new_status']); ?>
                            </strong>
                            <small class="text-muted"><?php echo formatDate($history['created_at'], 'M d, Y h:i A'); ?></small>
                        </div>
                        <small class="text-muted">
                            by <?php echo sanitize($history['changed_by_name'] ?? 'System'); ?>
                            <?php if (!empty($history['notes'])): ?>
                                - <?php echo sanitize($history['notes']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <hr>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Student Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-2"></i>Student Information
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> <?php echo sanitize($request['last_name'] . ', ' . $request['first_name']); ?></p>
                <p class="mb-1"><strong>Student #:</strong> <?php echo sanitize($request['student_number']); ?></p>
                <p class="mb-1"><strong>LRN:</strong> <?php echo sanitize($request['lrn'] ?? 'N/A'); ?></p>
                <p class="mb-1"><strong>Contact:</strong> <?php echo sanitize($request['contact_number'] ?? 'N/A'); ?></p>
                <p class="mb-0"><strong>Email:</strong> <?php echo sanitize($request['email'] ?? 'N/A'); ?></p>
                <hr>
                <a href="../students/view.php?id=<?php echo $request['student_id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                    <i class="fas fa-eye me-1"></i>View Full Profile
                </a>
            </div>
        </div>
        
        <!-- Update Status -->
        <?php if (in_array($request['status'], ['Pending', 'Approved', 'Processing', 'Ready for Release'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-sync me-2"></i>Update Status
            </div>
            <div class="card-body">
                <form method="POST" action="update_status.php">
                    <input type="hidden" name="request_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select" name="status" id="statusSelect" required>
                            <option value="">Select Status</option>
                            <?php
                            $workflow = [
                                'Pending' => ['Approved', 'Rejected', 'Cancelled'],
                                'Approved' => ['Processing', 'Rejected', 'Cancelled'],
                                'Processing' => ['Ready for Release', 'Cancelled'],
                                'Ready for Release' => ['Released']
                            ];
                            $currentStatus = $request['status'];
                            $nextStatuses = $workflow[$currentStatus] ?? [];
                            foreach ($nextStatuses as $status):
                            ?>
                            <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="releasedToGroup" style="display: none;">
                        <label class="form-label">Released To</label>
                        <input type="text" class="form-control" name="released_to" placeholder="Name of person who claimed">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- QR Code -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-qrcode me-2"></i>QR Code
            </div>
            <div class="card-body text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo APP_URL . '/requests/verify.php?tracking=' . urlencode($request['tracking_number']); ?>" 
                     alt="QR Code" class="img-fluid mb-2">
                <p class="text-muted small mb-0">Scan to verify request</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('statusSelect')?.addEventListener('change', function() {
    var releasedToGroup = document.getElementById('releasedToGroup');
    if (this.value === 'Released') {
        releasedToGroup.style.display = 'block';
    } else {
        releasedToGroup.style.display = 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
