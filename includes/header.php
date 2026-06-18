<?php
/**
 * DNHS Hub - Header Include
 * 
 * Main header and navigation bar
 */

// Ensure config is loaded
if (!function_exists('requireAuth')) {
    require_once __DIR__ . '/../config/config.php';
}

// Check session timeout
if (isLoggedIn()) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        setFlashMessage('warning', 'Session expired. Please login again.');
        redirect(APP_URL . '/login.php');
    }
    $_SESSION['last_activity'] = time();
}

requireAuth();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$unreadCount = getUnreadNotificationCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $pageTitle ?? 'DNHS Hub'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-school"></i>
                </div>
                <div class="sidebar-brand">
                    <h5>DNHS Hub</h5>
                    <small>Registrar's Office</small>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/users/index.php">
                            <i class="fas fa-users-cog"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['students', 'student_view', 'student_add', 'student_edit']) ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/students/index.php">
                            <i class="fas fa-user-graduate"></i>
                            <span>Student Records</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'archived' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/students/archived.php">
                            <i class="fas fa-archive"></i>
                            <span>Archived Students</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'documents' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/documents/index.php">
                            <i class="fas fa-folder-open"></i>
                            <span>Student Documents</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['requests', 'request_add', 'request_view']) ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/requests/index.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Document Requests</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'reports' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/reports/index.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'audit' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/audit/index.php">
                            <i class="fas fa-history"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'backup' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/backup/index.php">
                            <i class="fas fa-database"></i>
                            <span>Backup & Restore</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <small>&copy; <?php echo date('Y'); ?> DNHS Hub</small>
            </div>
        </div>
        
        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper" class="w-100">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg top-navbar">
                <div class="container-fluid">
                    <button class="btn btn-link" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex align-items-center ms-auto">
                        <!-- Search -->
                        <form class="d-none d-md-flex me-3" action="<?php echo APP_URL; ?>/students/index.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search students, requests...">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div id="notification-list">
                                    <?php
                                    $db = getDBConnection();
                                    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $notifications = $stmt->fetchAll();
                                    
                                    if (empty($notifications)):
                                    ?>
                                    <div class="dropdown-item text-muted text-center">No notifications</div>
                                    <?php else: ?>
                                    <?php foreach ($notifications as $notif): ?>
                                    <a class="dropdown-item notification-item <?php echo !$notif['is_read'] ? 'bg-light' : ''; ?>" href="<?php echo !empty($notif['link']) ? APP_URL . '/' . ltrim($notif['link'], './') : '#'; ?>" data-id="<?php echo $notif['id']; ?>">
                                        <small class="text-muted"><?php echo formatDate($notif['created_at'], 'M d, h:i A'); ?></small>
                                        <div><?php echo sanitize($notif['message']); ?></div>
                                    </a>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="<?php echo APP_URL; ?>/notifications/index.php">View All</a>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo sanitize($_SESSION['full_name']); ?>
                                <span class="badge bg-primary ms-1"><?php echo ucfirst($_SESSION['role']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Main Content -->
            <div class="container-fluid main-content">
                <?php displayFlashMessage(); ?>
