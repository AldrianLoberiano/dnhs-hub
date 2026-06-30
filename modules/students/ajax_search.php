<?php
require_once __DIR__ . '/../../config/config.php';
requireAuth();

header('Content-Type: application/json');
$db = getDBConnection();

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$batch = $_GET['batch'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;

$where = "WHERE s.is_archived = 0";
$params = [];

if (!empty($search)) {
    $where .= " AND (s.student_number LIKE ? OR s.lrn LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
    $p = "%$search%";
    $params = array_merge($params, [$p, $p, $p, $p, $p]);
}
if (!empty($status)) { $where .= " AND s.current_status = ?"; $params[] = $status; }
if (!empty($batch)) { $where .= " AND s.graduation_batch = ?"; $params[] = $batch; }

$stmt = $db->prepare("SELECT COUNT(*) as count FROM students s $where");
$stmt->execute($params);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

$stmt = $db->prepare("SELECT s.* FROM students s $where ORDER BY s.created_at DESC LIMIT $perPage OFFSET " . (int)$pagination['offset']);
$stmt->execute($params);
$students = $stmt->fetchAll();

$baseUrl = 'index.php?';
if (!empty($search)) $baseUrl .= "search=" . urlencode($search) . "&";
if (!empty($status)) $baseUrl .= "status=" . urlencode($status) . "&";
if (!empty($batch)) $baseUrl .= "batch=" . urlencode($batch) . "&";

ob_start();
if (empty($students)): ?>
    <tr><td colspan="7" class="text-center text-muted py-4">No students found</td></tr>
<?php else: foreach ($students as $student): ?>
    <tr>
        <td><code><?php echo sanitize($student['student_number']); ?></code></td>
        <td>
            <strong><?php echo sanitize($student['last_name'] . ', ' . $student['first_name']); ?></strong>
            <?php if (!empty($student['middle_name'])): ?>
                <br><small class="text-muted"><?php echo sanitize($student['middle_name']); ?></small>
            <?php endif; ?>
        </td>
        <td><?php echo sanitize($student['lrn'] ?? 'N/A'); ?></td>
        <td><?php echo sanitize($student['gender']); ?></td>
        <td>
            <?php
            $statusClass = ['Enrolled' => 'bg-success', 'Graduated' => 'bg-primary', 'Transferred' => 'bg-info', 'Dropped' => 'bg-danger', 'Archived' => 'bg-secondary'];
            ?>
            <span class="badge <?php echo $statusClass[$student['current_status']] ?? 'bg-secondary'; ?>">
                <?php echo sanitize($student['current_status']); ?>
            </span>
        </td>
        <td><?php echo sanitize($student['graduation_batch'] ?? 'N/A'); ?></td>
        <td>
            <div class="d-flex gap-1">
                <a href="view.php?id=<?php echo $student['id']; ?>" class="icon-btn" title="View"><i class="fas fa-eye"></i></a>
                <a href="edit.php?id=<?php echo $student['id']; ?>" class="icon-btn" title="Edit"><i class="fas fa-pen"></i></a>
                <form method="POST" action="archive.php" style="display:inline">
                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                    <button type="submit" class="icon-btn btn-confirm-archive" title="Archive"><i class="fas fa-archive"></i></button>
                </form>
            </div>
        </td>
    </tr>
<?php endforeach; endif;
$tableHtml = ob_get_clean();

ob_start(); echo renderPagination($pagination, $baseUrl);
$paginationHtml = ob_get_clean();

echo json_encode(['table' => $tableHtml, 'pagination' => $paginationHtml, 'total' => $total]);
