<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

header('Content-Type: application/json');
$db = getDBConnection();

$search = trim($_GET['search'] ?? '');
$docType = $_GET['doc_type'] ?? '');
$studentId = intval($_GET['student_id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR sd.original_name LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
    $p = "%$search%";
    $params = array_merge($params, [$p, $p, $p, $p, $p]);
}
if (!empty($docType)) { $where .= " AND sd.document_type_id = ?"; $params[] = intval($docType); }
if ($studentId > 0) { $where .= " AND sd.student_id = ?"; $params[] = $studentId; }

$stmt = $db->prepare("SELECT COUNT(*) as count FROM student_documents sd JOIN students s ON sd.student_id = s.id $where");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

$stmt = $db->prepare("
    SELECT sd.*, s.student_number, s.first_name, s.last_name, dt.name as doc_type_name
    FROM student_documents sd 
    JOIN students s ON sd.student_id = s.id 
    LEFT JOIN document_types dt ON sd.document_type_id = dt.id 
    $where ORDER BY sd.created_at DESC LIMIT $perPage OFFSET " . (int)$pagination['offset']
);
$stmt->execute($params);
$documents = $stmt->fetchAll();

$baseUrl = 'index.php?';
if (!empty($search)) $baseUrl .= "search=" . urlencode($search) . "&";
if (!empty($docType)) $baseUrl .= "doc_type=" . urlencode($docType) . "&";
if ($studentId) $baseUrl .= "student_id=$studentId&";

ob_start();
if (empty($documents)): ?>
    <tr><td colspan="7" class="text-center text-muted py-4">No documents found</td></tr>
<?php else: foreach ($documents as $doc): ?>
    <tr>
        <td>
            <a href="../students/view.php?id=<?php echo $doc['student_id']; ?>">
                <?php echo sanitize($doc['first_name'] . ' ' . $doc['last_name']); ?>
            </a>
            <br><small class="text-muted"><?php echo sanitize($doc['student_number']); ?></small>
        </td>
        <td><?php echo sanitize($doc['doc_type_name']); ?></td>
        <td><?php echo sanitize($doc['original_name']); ?></td>
        <td>v<?php echo $doc['version']; ?></td>
        <td><?php echo round($doc['file_size'] / 1024, 1); ?> KB</td>
        <td><?php echo formatDate($doc['created_at']); ?></td>
        <td>
            <a href="download.php?id=<?php echo $doc['id']; ?>" class="icon-btn" title="Download"><i class="fas fa-download"></i></a>
            <button type="button" class="icon-btn btn-delete-doc" data-id="<?php echo $doc['id']; ?>" title="Delete"><i class="fas fa-trash text-danger"></i></button>
        </td>
    </tr>
<?php endforeach; endif;
$tableHtml = ob_get_clean();

ob_start(); echo renderPagination($pagination, $baseUrl);
$paginationHtml = ob_get_clean();

echo json_encode(['table' => $tableHtml, 'pagination' => $paginationHtml, 'total' => $total]);
