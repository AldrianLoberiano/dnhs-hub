<?php
/**
 * DNHS Hub - Dashboard
 * 
 * Main dashboard with summary cards and charts
 */

$pageTitle = 'Dashboard - DNHS Hub';
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Get summary counts
$counts = [];

// Total Students
$stmt = $db->query("SELECT COUNT(*) as count FROM students WHERE is_archived = 0");
$counts['students'] = $stmt->fetch()['count'];

// Total Requests
$stmt = $db->query("SELECT COUNT(*) as count FROM document_requests");
$counts['total_requests'] = $stmt->fetch()['count'];

// Pending Requests
$stmt = $db->query("SELECT COUNT(*) as count FROM document_requests WHERE status = 'Pending'");
$counts['pending'] = $stmt->fetch()['count'];

// Processing Requests
$stmt = $db->query("SELECT COUNT(*) as count FROM document_requests WHERE status = 'Processing'");
$counts['processing'] = $stmt->fetch()['count'];

// Ready for Release
$stmt = $db->query("SELECT COUNT(*) as count FROM document_requests WHERE status = 'Ready for Release'");
$counts['ready'] = $stmt->fetch()['count'];

// Released Documents
$stmt = $db->query("SELECT COUNT(*) as count FROM document_requests WHERE status = 'Released'");
$counts['released'] = $stmt->fetch()['count'];

// Total Uploaded Documents
$stmt = $db->query("SELECT COUNT(*) as count FROM student_documents");
$counts['documents'] = $stmt->fetch()['count'];

// Active Users
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
$counts['users'] = $stmt->fetch()['count'];

// Monthly Requests for chart (last 12 months)
$stmt = $db->query("
    SELECT DATE_FORMAT(date_requested, '%Y-%m') as month, COUNT(*) as count 
    FROM document_requests 
    WHERE date_requested >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY month 
    ORDER BY month
");
$monthlyData = $stmt->fetchAll();

// Most Requested Documents
$stmt = $db->query("
    SELECT dt.name, COUNT(dr.id) as count 
    FROM document_requests dr 
    JOIN document_types dt ON dr.document_type_id = dt.id 
    GROUP BY dt.name 
    ORDER BY count DESC 
    LIMIT 5
");
$topDocuments = $stmt->fetchAll();

// Request Status Breakdown
$stmt = $db->query("
    SELECT status, COUNT(*) as count 
    FROM document_requests 
    GROUP BY status
");
$statusBreakdown = $stmt->fetchAll();

// Recent Requests
$stmt = $db->query("
    SELECT dr.*, s.first_name, s.last_name, dt.name as doc_type 
    FROM document_requests dr 
    JOIN students s ON dr.student_id = s.id 
    JOIN document_types dt ON dr.document_type_id = dt.id 
    ORDER BY dr.created_at DESC 
    LIMIT 5
");
$recentRequests = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h4>Dashboard</h4>
        <small style="color: rgba(255,255,255,0.8);">Overview of student records and document requests</small>
    </div>
    <span style="color: rgba(255,255,255,0.8);">Welcome, <?php echo sanitize($_SESSION['full_name']); ?></span>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <a href="students/index.php" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #0D6EFD;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['students']); ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <a href="requests/index.php" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #6c757d;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['total_requests']); ?></h3>
                    <p>Total Requests</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <a href="requests/index.php?status=Pending" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #ffc107;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['pending']); ?></h3>
                    <p>Pending Requests</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <a href="requests/index.php?status=Processing" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #17a2b8;">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['processing']); ?></h3>
                    <p>Processing</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <a href="requests/index.php?status=Ready for Release" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #28a745;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['ready']); ?></h3>
                    <p>Ready for Release</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <a href="requests/index.php?status=Released" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #343a40;">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['released']); ?></h3>
                    <p>Released</p>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <a href="documents/index.php" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #fd7e14;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['documents']); ?></h3>
                    <p>Uploaded Documents</p>
                </div>
            </div>
        </a>
    </div>
    
    <?php if (isAdmin()): ?>
    <div class="col-xl-3 col-md-6">
        <a href="users/index.php" class="text-decoration-none">
            <div class="summary-card">
                <div class="icon" style="background: #e83e8c;">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="info">
                    <h3><?php echo number_format($counts['users']); ?></h3>
                    <p>Active Users</p>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>Monthly Requests
            </div>
            <div class="card-body">
                <canvas id="monthlyRequestsChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Request Status
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- More Charts & Recent Activity -->
<div class="row g-3">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i>Most Requested Documents
            </div>
            <div class="card-body">
                <canvas id="topDocsChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Recent Requests
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tracking #</th>
                                <th>Student</th>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentRequests)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No requests yet</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentRequests as $req): ?>
                            <tr>
                                <td><small><?php echo sanitize($req['tracking_number']); ?></small></td>
                                <td><?php echo sanitize($req['first_name'] . ' ' . $req['last_name']); ?></td>
                                <td><?php echo sanitize($req['doc_type']); ?></td>
                                <td><span class="badge <?php echo getStatusBadgeClass($req['status']); ?>"><?php echo sanitize($req['status']); ?></span></td>
                                <td><small><?php echo formatDate($req['date_requested']); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Requests Chart
var monthlyCtx = document.getElementById('monthlyRequestsChart').getContext('2d');
var monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyData, 'month')); ?>,
        datasets: [{
            label: 'Requests',
            data: <?php echo json_encode(array_column($monthlyData, 'count')); ?>,
            borderColor: '#0D6EFD',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Status Breakdown Chart
var statusCtx = document.getElementById('statusChart').getContext('2d');
var statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($statusBreakdown, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($statusBreakdown, 'count')); ?>,
            backgroundColor: ['#ffc107', '#17a2b8', '#0D6EFD', '#28a745', '#6c757d', '#dc3545', '#343a40']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Top Documents Chart
var topCtx = document.getElementById('topDocsChart').getContext('2d');
var topChart = new Chart(topCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($topDocuments, 'name')); ?>,
        datasets: [{
            label: 'Requests',
            data: <?php echo json_encode(array_column($topDocuments, 'count')); ?>,
            backgroundColor: '#0D6EFD'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
