<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

header('Content-Type: application/json');
$db = getDBConnection();

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$docType = $_GET['doc_type'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (dr.tracking_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_number LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
    $p = "%$search%";
    $params = array_merge($params, [$p, $p, $p, $p, $p]);
}
if (!empty($status)) { $where .= " AND dr.status = ?"; $params[] = $status; }
if (!empty($docType)) { $where .= " AND dr.document_type_id = ?"; $params[] = intval($docType); }

$stmt = $db->prepare("SELECT COUNT(*) as count FROM document_requests dr JOIN students s ON dr.student_id = s.id $where");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

$stmt = $db->prepare("
    SELECT dr.*, s.first_name, s.last_name, s.student_number, dt.name as doc_type_name, 
           CONCAT(u.first_name, ' ', u.last_name) as registrar_name
    FROM document_requests dr 
    JOIN students s ON dr.student_id = s.id 
    JOIN document_types dt ON dr.document_type_id = dt.id 
    LEFT JOIN users u ON dr.requested_by = u.id 
    $where ORDER BY dr.created_at DESC LIMIT $perPage OFFSET " . (int)$pagination['offset']
);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$baseUrl = 'index.php?';
if (!empty($search)) $baseUrl .= "search=" . urlencode($search) . "&";
if (!empty($status)) $baseUrl .= "status=" . urlencode($status) . "&";
if (!empty($docType)) $baseUrl .= "doc_type=" . urlencode($docType) . "&";

ob_start();
if (empty($requests)): ?>
    <tr><td colspan="8" class="text-center text-muted py-4">No requests found</td></tr>
<?php else: foreach ($requests as $req): ?>
    <tr>
        <td><code><?php echo sanitize($req['tracking_number']); ?></code></td>
        <td>
            <a href="../students/view.php?id=<?php echo $req['student_id']; ?>">
                <?php echo sanitize($req['last_name'] . ', ' . $req['first_name']); ?>
            </a>
            <br><small class="text-muted"><?php echo sanitize($req['student_number']); ?></small>
        </td>
        <td><?php echo sanitize($req['doc_type_name']); ?></td>
        <td><?php echo sanitize($req['purpose'] ?? 'N/A'); ?></td>
        <td class="text-start"><span class="badge <?php echo getStatusBadgeClass($req['status']); ?>"><?php echo sanitize($req['status']); ?></span></td>
        <td><?php echo formatDate($req['date_requested']); ?></td>
        <td><?php echo sanitize($req['registrar_name'] ?? 'N/A'); ?></td>
        <td>
            <a href="view.php?id=<?php echo $req['id']; ?>" class="icon-btn" title="View"><i class="fas fa-eye"></i></a>
        </td>
    </tr>
<?php endforeach; endif;
$tableHtml = ob_get_clean();

ob_start(); echo renderPagination($pagination, $baseUrl);
$paginationHtml = ob_get_clean();

echo json_encode(['table' => $tableHtml, 'pagination' => $paginationHtml, 'total' => $total]);
