<?php
/**
 * DNHS Hub - Notifications
 * 
 * Display all notifications
 */

$pageTitle = 'Notifications - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';

$db = getDBConnection();

// Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid security token.');
        redirect(APP_URL . '/notifications/index.php');
    }
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    setFlashMessage('success', 'All notifications marked as read.');
    redirect(APP_URL . '/notifications/index.php');
}

// Get notifications
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

$stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total = $stmt->fetch()['count'];
$pagination = getPagination($total, $perPage, $page);

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$pagination['offset']);
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h4>Notifications</h4>
        <small style="color: rgba(255,255,255,0.8);">View all your notifications</small>
    </div>
    <form method="POST" action="" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
        <input type="hidden" name="mark_all_read" value="1">
        <button type="submit" class="btn btn-outline-primary">
            <i class="fas fa-check-double me-1"></i>Mark All as Read
        </button>
    </form>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($notifications)): ?>
        <p class="text-center text-muted py-4">No notifications</p>
        <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $notif): ?>
            <?php $notifUrl = !empty($notif['link']) && preg_match('#^[a-zA-Z0-9/_-]+$#', $notif['link']) ? $notif['link'] : '#'; ?>
            <a href="<?php echo $notifUrl; ?>" class="list-group-item list-group-item-action <?php echo !$notif['is_read'] ? 'bg-light' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1"><?php echo sanitize($notif['title']); ?></h6>
                        <p class="mb-1"><?php echo sanitize($notif['message']); ?></p>
                    </div>
                    <small class="text-muted"><?php echo formatDate($notif['created_at'], 'M d, h:i A'); ?></small>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <?php echo renderPagination($pagination, 'index.php?'); ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
