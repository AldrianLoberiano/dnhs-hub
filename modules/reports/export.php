<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

$db = getDBConnection();

$reportType = $_GET['type'] ?? 'daily';
$startDate = isset($_GET['start_date']) ? date('Y-m-d', strtotime($_GET['start_date'])) : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? date('Y-m-d', strtotime($_GET['end_date'])) : date('Y-m-d');
$format = $_GET['format'] ?? 'csv';

$reportData = [];
$reportTitle = '';
$headers = [];

switch ($reportType) {
    case 'daily':
        $reportTitle = 'Daily Requests Report - ' . formatDate($startDate);
        $headers = ['Tracking #', 'Student Name', 'Student #', 'Document Type', 'Status', 'Date'];
        $stmt = $db->prepare("
            SELECT dr.tracking_number, CONCAT(s.last_name, ', ', s.first_name) as student_name, s.student_number, dt.name as doc_type_name, dr.status, dr.date_requested
            FROM document_requests dr 
            JOIN students s ON dr.student_id = s.id 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            WHERE dr.date_requested = ?
            ORDER BY dr.created_at DESC LIMIT 200
        ");
        $stmt->execute([$startDate]);
        $reportData = $stmt->fetchAll();
        break;
        
    case 'weekly':
        $reportTitle = 'Weekly Requests Report - ' . formatDate($startDate) . ' to ' . formatDate($endDate);
        $headers = ['Tracking #', 'Student Name', 'Student #', 'Document Type', 'Status', 'Date'];
        $stmt = $db->prepare("
            SELECT dr.tracking_number, CONCAT(s.last_name, ', ', s.first_name) as student_name, s.student_number, dt.name as doc_type_name, dr.status, dr.date_requested
            FROM document_requests dr 
            JOIN students s ON dr.student_id = s.id 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            WHERE dr.date_requested BETWEEN ? AND ?
            ORDER BY dr.date_requested DESC LIMIT 200
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();
        break;
        
    case 'monthly':
        $reportTitle = 'Monthly Requests Report - ' . formatDate($startDate) . ' to ' . formatDate($endDate);
        $headers = ['Tracking #', 'Student Name', 'Student #', 'Document Type', 'Status', 'Date'];
        $stmt = $db->prepare("
            SELECT dr.tracking_number, CONCAT(s.last_name, ', ', s.first_name) as student_name, s.student_number, dt.name as doc_type_name, dr.status, dr.date_requested
            FROM document_requests dr 
            JOIN students s ON dr.student_id = s.id 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            WHERE dr.date_requested BETWEEN ? AND ?
            ORDER BY dr.date_requested DESC LIMIT 200
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();
        break;
        
    case 'status':
        $reportTitle = 'Request Status Breakdown';
        $headers = ['Status', 'Count', 'Percentage'];
        $stmt = $db->query("
            SELECT status, COUNT(*) as count 
            FROM document_requests 
            GROUP BY status 
            ORDER BY FIELD(status, 'Pending', 'Approved', 'Processing', 'Ready for Release', 'Released', 'Rejected', 'Cancelled')
        ");
        $reportData = $stmt->fetchAll();
        $total = array_sum(array_column($reportData, 'count'));
        foreach ($reportData as &$row) {
            $row['percentage'] = $total > 0 ? round(($row['count'] / $total) * 100, 1) . '%' : '0%';
        }
        break;
        
    case 'top_documents':
        $reportTitle = 'Most Requested Documents';
        $headers = ['Document Type', 'Request Count'];
        $stmt = $db->query("
            SELECT dt.name, COUNT(dr.id) as count 
            FROM document_requests dr 
            JOIN document_types dt ON dr.document_type_id = dt.id 
            GROUP BY dt.name ORDER BY count DESC LIMIT 10
        ");
        $reportData = $stmt->fetchAll();
        break;
        
    case 'registrar_activity':
        $reportTitle = 'Registrar Activity Report';
        $headers = ['Registrar', 'Total Requests', 'Released'];
        $stmt = $db->query("
            SELECT CONCAT(u.first_name, ' ', u.last_name) as name, COUNT(dr.id) as request_count,
                   SUM(CASE WHEN dr.status = 'Released' THEN 1 ELSE 0 END) as released_count
            FROM document_requests dr 
            JOIN users u ON dr.requested_by = u.id 
            GROUP BY u.id ORDER BY request_count DESC LIMIT 20
        ");
        $reportData = $stmt->fetchAll();
        break;
}

$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $reportTitle);

switch ($format) {
    case 'csv':
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($output, $headers);
        foreach ($reportData as $row) {
            fputcsv($output, array_values($row));
        }
        fclose($output);
        break;
        
    case 'excel':
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.xls\"");
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="UTF-8">';
        echo '<style>table{border-collapse:collapse}th,td{border:1px solid #333;padding:6px 12px;text-align:left}th{background:#198754;color:#fff;font-weight:bold}tr:nth-child(even){background:#f2f2f2}</style>';
        echo '</head><body>';
        echo '<h2>' . htmlspecialchars($reportTitle) . '</h2>';
        echo '<table><thead><tr>';
        foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($reportData as $row) {
            echo '<tr>';
            foreach ($row as $cell) echo '<td>' . htmlspecialchars($cell) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p style="color:#666;font-size:12px;margin-top:10px">Generated: ' . date('M d, Y h:i A') . ' | DNHS Hub</p>';
        echo '</body></html>';
        break;
        
    case 'word':
        header('Content-Type: application/msword; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}.doc\"");
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">';
        echo '<head><meta charset="UTF-8">';
        echo '<style>table{border-collapse:collapse}th,td{border:1px solid #333;padding:6px 12px;text-align:left}th{background:#198754;color:#fff;font-weight:bold}tr:nth-child(even){background:#f2f2f2}</style>';
        echo '</head><body>';
        echo '<h2>' . htmlspecialchars($reportTitle) . '</h2>';
        echo '<table><thead><tr>';
        foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($reportData as $row) {
            echo '<tr>';
            foreach ($row as $cell) echo '<td>' . htmlspecialchars($cell) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<br><p style="color:#666;font-size:12px">Generated: ' . date('M d, Y h:i A') . ' | DNHS Hub</p>';
        echo '</body></html>';
        break;
}
exit;
