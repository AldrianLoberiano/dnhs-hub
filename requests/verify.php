<?php
/**
 * DNHS Hub - Verify Request
 * 
 * QR code verification page for document requests
 */

require_once __DIR__ . '/../config/config.php';

$trackingNumber = trim($_GET['tracking'] ?? '');

if (empty($trackingNumber)) {
    die("Invalid tracking number.");
}

$db = getDBConnection();

$stmt = $db->prepare("
    SELECT dr.*, s.first_name, s.last_name, s.student_number, s.lrn,
           dt.name as doc_type_name,
           CONCAT(u.first_name, ' ', u.last_name) as registrar_name
    FROM document_requests dr 
    JOIN students s ON dr.student_id = s.id 
    JOIN document_types dt ON dr.document_type_id = dt.id 
    LEFT JOIN users u ON dr.requested_by = u.id 
    WHERE dr.tracking_number = ?
");
$stmt->execute([$trackingNumber]);
$request = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Request - DNHS Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; padding: 40px 20px; }
        .verify-card { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="text-center mb-4">
            <i class="fas fa-school fa-3x text-primary mb-3"></i>
            <h4>DNHS Hub</h4>
            <p class="text-muted">Request Verification</p>
        </div>
        
        <?php if (!$request): ?>
        <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Request not found. Please check the tracking number.
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong><?php echo sanitize($request['tracking_number']); ?></strong></span>
                <span class="badge <?php echo getStatusBadgeClass($request['status']); ?>"><?php echo sanitize($request['status']); ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Student Name</strong>
                        <p class="mb-0"><?php echo sanitize($request['last_name'] . ', ' . $request['first_name']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Student Number</strong>
                        <p class="mb-0"><?php echo sanitize($request['student_number']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>LRN</strong>
                        <p class="mb-0"><?php echo sanitize($request['lrn'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Document Type</strong>
                        <p class="mb-0"><?php echo sanitize($request['doc_type_name']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Date Requested</strong>
                        <p class="mb-0"><?php echo formatDate($request['date_requested']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Expected Release</strong>
                        <p class="mb-0"><?php echo !empty($request['expected_release_date']) ? formatDate($request['expected_release_date']) : 'N/A'; ?></p>
                    </div>
                    <?php if ($request['status'] === 'Released'): ?>
                    <div class="col-md-6">
                        <strong>Released Date</strong>
                        <p class="mb-0 text-success"><?php echo formatDate($request['actual_release_date']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Released To</strong>
                        <p class="mb-0 text-success"><?php echo sanitize($request['released_to'] ?? 'N/A'); ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-12">
                        <strong>Purpose</strong>
                        <p class="mb-0"><?php echo sanitize($request['purpose'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-12">
                        <strong>Registrar Assigned</strong>
                        <p class="mb-0"><?php echo sanitize($request['registrar_name'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>
                This is an official verification page of DNHS Hub
            </small>
        </div>
    </div>
</body>
</html>
