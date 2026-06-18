<?php
/**
 * DNHS Hub - Reports
 * 
 * Generate various reports
 */

$pageTitle = 'Reports - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

$reportType = $_GET['type'] ?? 'daily';
$startDate = $_GET['start_date'] ?? date('Y-m-d');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$reportData = [];
$reportTitle = '';

switch ($reportType) {
    case 'daily':
        $reportTitle = 'Daily Requests Report';
        $stmt = $db->prepare("
            SELECT dr.*, s.first_name, s.last_name, s.student_number, dt.name as doc_type_name
            FROM document_requests dr 
            JOIN students s ON dr.student_id = s.id 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            WHERE dr.date_requested = ?
            ORDER BY dr.created_at DESC
        ");
        $stmt->execute([$startDate]);
        $reportData = $stmt->fetchAll();
        break;
        
    case 'weekly':
        $reportTitle = 'Weekly Requests Report';
        $stmt = $db->prepare("
            SELECT dr.*, s.first_name, s.last_name, s.student_number, dt.name as doc_type_name
            FROM document_requests dr 
            JOIN students s ON dr.student_id = s.id 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            WHERE dr.date_requested BETWEEN ? AND ?
            ORDER BY dr.date_requested DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();
        break;
        
    case 'monthly':
        $reportTitle = 'Monthly Requests Report';
        $stmt = $db->prepare("
            SELECT dr.*, s.first_name, s.last_name, s.student_number, dt.name as doc_type_name
            FROM document_requests dr 
            JOIN students s ON dr.student_id = s.id 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            WHERE dr.date_requested BETWEEN ? AND ?
            ORDER BY dr.date_requested DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();
        break;
        
    case 'status':
        $reportTitle = 'Request Status Report';
        $stmt = $db->query("
            SELECT status, COUNT(*) as count 
            FROM document_requests 
            GROUP BY status 
            ORDER BY FIELD(status, 'Pending', 'Approved', 'Processing', 'Ready for Release', 'Released', 'Rejected', 'Cancelled')
        ");
        $reportData = $stmt->fetchAll();
        break;
        
    case 'top_documents':
        $reportTitle = 'Most Requested Documents';
        $stmt = $db->query("
            SELECT dt.name, COUNT(dr.id) as count 
            FROM document_requests dr 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            GROUP BY dt.name 
            ORDER BY count DESC 
            LIMIT 10
        ");
        $reportData = $stmt->fetchAll();
        break;
        
    case 'registrar_activity':
        $reportTitle = 'Registrar Activity Report';
        $stmt = $db->query("
            SELECT u.first_name, u.last_name, COUNT(dr.id) as request_count,
                   SUM(CASE WHEN dr.status = 'Released' THEN 1 ELSE 0 END) as released_count
            FROM document_requests dr 
            JOIN users u ON dr.requested_by = u.id 
            GROUP BY u.id 
            ORDER BY request_count DESC
        ");
        $reportData = $stmt->fetchAll();
        break;
}
?>

<div class="page-header">
    <div>
        <h4>Reports</h4>
        <small style="color: rgba(255,255,255,0.8);">Generate and view system reports</small>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary no-print">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>
</div>

<!-- Report Filters -->
<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select class="form-select" name="type">
                    <option value="daily" <?php echo $reportType === 'daily' ? 'selected' : ''; ?>>Daily Requests</option>
                    <option value="weekly" <?php echo $reportType === 'weekly' ? 'selected' : ''; ?>>Weekly Requests</option>
                    <option value="monthly" <?php echo $reportType === 'monthly' ? 'selected' : ''; ?>>Monthly Requests</option>
                    <option value="status" <?php echo $reportType === 'status' ? 'selected' : ''; ?>>Request Status Breakdown</option>
                    <option value="top_documents" <?php echo $reportType === 'top_documents' ? 'selected' : ''; ?>>Most Requested Documents</option>
                    <option value="registrar_activity" <?php echo $reportType === 'registrar_activity' ? 'selected' : ''; ?>>Registrar Activity</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Report Content -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-file-alt me-2"></i><?php echo $reportTitle; ?>
        <small class="text-muted ms-2">
            <?php if (in_array($reportType, ['daily', 'weekly', 'monthly'])): ?>
                (<?php echo formatDate($startDate); ?> - <?php echo formatDate($endDate); ?>)
            <?php endif; ?>
        </small>
    </div>
    <div class="card-body">
        <?php if (empty($reportData)): ?>
        <p class="text-center text-muted py-4">No data available for this report</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <?php if (in_array($reportType, ['daily', 'weekly', 'monthly'])): ?>
                    <tr>
                        <th>Tracking #</th>
                        <th>Student</th>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                    <?php elseif ($reportType === 'status'): ?>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                    <?php elseif ($reportType === 'top_documents'): ?>
                    <tr>
                        <th>Document Type</th>
                        <th>Request Count</th>
                    </tr>
                    <?php elseif ($reportType === 'registrar_activity'): ?>
                    <tr>
                        <th>Registrar</th>
                        <th>Total Requests</th>
                        <th>Released</th>
                    </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (in_array($reportType, ['daily', 'weekly', 'monthly'])): ?>
                        <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td><code><?php echo sanitize($row['tracking_number']); ?></code></td>
                            <td><?php echo sanitize($row['last_name'] . ', ' . $row['first_name']); ?></td>
                            <td><?php echo sanitize($row['doc_type_name']); ?></td>
                            <td><span class="badge <?php echo getStatusBadgeClass($row['status']); ?>"><?php echo sanitize($row['status']); ?></span></td>
                            <td><?php echo formatDate($row['date_requested']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php elseif ($reportType === 'status'): ?>
                        <?php
                        $total = array_sum(array_column($reportData, 'count'));
                        foreach ($reportData as $row):
                        ?>
                        <tr>
                            <td><span class="badge <?php echo getStatusBadgeClass($row['status']); ?>"><?php echo sanitize($row['status']); ?></span></td>
                            <td><?php echo $row['count']; ?></td>
                            <td><?php echo $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php elseif ($reportType === 'top_documents'): ?>
                        <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td><?php echo sanitize($row['name']); ?></td>
                            <td><?php echo $row['count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php elseif ($reportType === 'registrar_activity'): ?>
                        <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td><?php echo sanitize($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo $row['request_count']; ?></td>
                            <td><?php echo $row['released_count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
