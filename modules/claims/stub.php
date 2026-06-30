<?php
/**
 * DNHS Hub - Claim Stub
 * 
 * Generate and display claim stub for document requests
 */

require_once __DIR__ . '/../../config/config.php';
requireAuth();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid request ID.');
    redirect(APP_URL . '/modules/index.php');
}

// Get request details
$stmt = $db->prepare("
    SELECT dr.*, s.first_name, s.last_name, s.student_number, s.lrn, s.contact_number,
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
    redirect(APP_URL . '/modules/index.php');
}

$qrUrl = APP_URL . "/requests/verify.php?tracking=" . urlencode($request['tracking_number']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Stub - <?php echo sanitize($request['tracking_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; padding: 20px; font-family: 'Segoe UI', sans-serif; }
        .claim-stub { 
            max-width: 400px; 
            margin: 0 auto; 
            background: #fff; 
            border: 2px solid #212529; 
            padding: 20px;
        }
        .claim-stub .header { text-align: center; border-bottom: 2px solid #212529; padding-bottom: 15px; margin-bottom: 15px; }
        .claim-stub .school-name { font-size: 18px; font-weight: bold; }
        .claim-stub .office-name { font-size: 12px; color: #666; }
        .claim-stub .tracking { font-size: 14px; font-weight: bold; text-align: center; background: #f0f0f0; padding: 8px; margin: 10px 0; }
        .claim-stub .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
        .claim-stub .info-label { font-weight: bold; }
        .claim-stub .qr-section { text-align: center; margin: 15px 0; }
        .claim-stub .signatures { display: flex; justify-content: space-between; margin-top: 30px; font-size: 11px; }
        .claim-stub .signature-box { text-align: center; width: 45%; }
        .claim-stub .signature-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .claim-stub { border: 2px solid #000; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i>Print Claim Stub
        </button>
        <a href="../requests/view.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Request
        </a>
    </div>
    
    <div class="claim-stub">
        <div class="header">
            <div class="school-name">DITA NATIONAL HIGH SCHOOL</div>
            <div class="office-name">Registrar's Office</div>
            <div class="office-name">Dita, Alabel, Sarangani Province</div>
        </div>
        
        <div class="tracking">
            Tracking Number: <?php echo sanitize($request['tracking_number']); ?>
        </div>
        
        <div class="info-row">
            <span class="info-label">Student Name:</span>
            <span><?php echo sanitize($request['last_name'] . ', ' . $request['first_name']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Student Number:</span>
            <span><?php echo sanitize($request['student_number']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">LRN:</span>
            <span><?php echo sanitize($request['lrn'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Document Requested:</span>
            <span><?php echo sanitize($request['doc_type_name']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Purpose:</span>
            <span><?php echo sanitize($request['purpose'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date Requested:</span>
            <span><?php echo formatDate($request['date_requested']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Expected Release:</span>
            <span><?php echo !empty($request['expected_release_date']) ? formatDate($request['expected_release_date']) : 'TBD'; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="badge <?php echo getStatusBadgeClass($request['status']); ?>"><?php echo sanitize($request['status']); ?></span>
        </div>
        
        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo urlencode($qrUrl); ?>" alt="QR Code">
            <div style="font-size: 10px; color: #666;">Scan to verify request</div>
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    Registrar Signature
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    Claimant Signature
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3" style="font-size: 10px; color: #666;">
            <small>This stub must be presented upon claiming the document.</small><br>
            <small>Keep this stub for your records.</small>
        </div>
    </div>
</body>
</html>
