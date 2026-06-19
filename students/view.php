<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDBConnection();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid student ID.');
    redirect(APP_URL . '/students/index.php');
}

$stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    setFlashMessage('error', 'Student not found.');
    redirect(APP_URL . '/students/index.php');
}

$pageTitle = sanitize($student['last_name'] . ', ' . $student['first_name']) . ' - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';

// Get student documents
$stmt = $db->prepare("
    SELECT sd.*, dt.name as doc_type_name 
    FROM student_documents sd 
    JOIN document_types dt ON sd.document_type_id = dt.id 
    WHERE sd.student_id = ? 
    ORDER BY sd.created_at DESC
");
$stmt->execute([$id]);
$documents = $stmt->fetchAll();

// Get request history
$stmt = $db->prepare("
    SELECT dr.*, dt.name as doc_type_name, u.first_name as registrar_first, u.last_name as registrar_last 
    FROM document_requests dr 
    JOIN document_types dt ON dr.document_type_id = dt.id 
    LEFT JOIN users u ON dr.requested_by = u.id 
    WHERE dr.student_id = ? 
    ORDER BY dr.created_at DESC
");
$stmt->execute([$id]);
$requests = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h4><?php echo sanitize($student['first_name'] . ' ' . $student['last_name']); ?>
            <?php if ($student['is_archived']): ?>
                <span class="badge bg-secondary ms-2">Archived</span>
            <?php endif; ?>
        </h4>
        <small style="color: rgba(255,255,255,0.8);">Complete student profile and document history</small>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary no-print">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>
</div>

<div class="row">
    <!-- Student Profile -->
    <div class="col-lg-8">
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-2"></i>Personal Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>Student Number</strong>
                        <p class="mb-0"><code><?php echo sanitize($student['student_number']); ?></code></p>
                    </div>
                    <div class="col-md-4">
                        <strong>LRN</strong>
                        <p class="mb-0"><?php echo sanitize($student['lrn'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Gender</strong>
                        <p class="mb-0"><?php echo sanitize($student['gender']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Full Name</strong>
                        <p class="mb-0">
                            <?php echo sanitize($student['first_name']); ?>
                            <?php echo !empty($student['middle_name']) ? sanitize($student['middle_name']) . ' ' : ''; ?>
                            <?php echo sanitize($student['last_name']); ?>
                            <?php echo !empty($student['suffix']) ? sanitize($student['suffix']) : ''; ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <strong>Birth Date</strong>
                        <p class="mb-0"><?php echo !empty($student['birth_date']) ? formatDate($student['birth_date']) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Birth Place</strong>
                        <p class="mb-0"><?php echo sanitize($student['birth_place'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Nationality</strong>
                        <p class="mb-0"><?php echo sanitize($student['nationality'] ?? 'Filipino'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Civil Status</strong>
                        <p class="mb-0"><?php echo sanitize($student['civil_status'] ?? 'Single'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Contact Number</strong>
                        <p class="mb-0"><?php echo sanitize($student['contact_number'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Email Address</strong>
                        <p class="mb-0"><?php echo sanitize($student['email'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Home Address</strong>
                        <p class="mb-0"><?php echo sanitize($student['home_address'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Guardian Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-users me-2"></i>Guardian Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>Parent Name</strong>
                        <p class="mb-0"><?php echo sanitize($student['parent_name'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Guardian Name</strong>
                        <p class="mb-0"><?php echo sanitize($student['guardian_name'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Guardian Contact</strong>
                        <p class="mb-0"><?php echo sanitize($student['guardian_contact'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Academic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-graduation-cap me-2"></i>Academic Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <strong>Current Status</strong>
                        <p class="mb-0">
                            <?php
                            $statusClass = [
                                'Enrolled' => 'bg-success',
                                'Graduated' => 'bg-primary',
                                'Transferred' => 'bg-info',
                                'Dropped' => 'bg-danger'
                            ];
                            ?>
                            <span class="badge <?php echo $statusClass[$student['current_status']] ?? 'bg-secondary'; ?>">
                                <?php echo sanitize($student['current_status']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <strong>School Year</strong>
                        <p class="mb-0"><?php echo sanitize($student['school_year'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Section</strong>
                        <p class="mb-0"><?php echo sanitize($student['section'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Strand</strong>
                        <p class="mb-0"><?php echo sanitize($student['strand'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Year Graduated</strong>
                        <p class="mb-0"><?php echo sanitize($student['year_graduated'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Graduation Batch</strong>
                        <p class="mb-0"><?php echo sanitize($student['graduation_batch'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Enrollment History</strong>
                        <p class="mb-0"><?php echo nl2br(sanitize($student['enrollment_history'] ?? 'N/A')); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Remarks</strong>
                        <p class="mb-0"><?php echo nl2br(sanitize($student['remarks'] ?? 'N/A')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Documents -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-folder-open me-2"></i>Documents</span>
                <span class="badge bg-primary"><?php echo count($documents); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($documents)): ?>
                <p class="text-muted text-center mb-0">No documents uploaded</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($documents, 0, 10) as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <small>
                            <i class="fas fa-file-<?php echo $doc['file_type'] === 'pdf' ? 'pdf text-danger' : 'image text-primary'; ?> me-2"></i>
                            <?php echo sanitize($doc['doc_type_name']); ?>
                            <br><span class="text-muted">v<?php echo $doc['version']; ?> - <?php echo formatDate($doc['created_at']); ?></span>
                        </small>
                        <a href="../documents/download.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($documents) > 10): ?>
                <a href="../documents/index.php?student_id=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">View All</a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Request History -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-alt me-2"></i>Request History</span>
                <span class="badge bg-primary"><?php echo count($requests); ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($requests)): ?>
                <p class="text-muted text-center mb-0">No requests yet</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($requests, 0, 5) as $req): ?>
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <small><strong><?php echo sanitize($req['doc_type_name']); ?></strong></small>
                            <span class="badge <?php echo getStatusBadgeClass($req['status']); ?>" style="font-size: 10px;"><?php echo sanitize($req['status']); ?></span>
                        </div>
                        <small class="text-muted">
                            <?php echo sanitize($req['tracking_number']); ?><br>
                            <?php echo formatDate($req['date_requested']); ?>
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($requests) > 5): ?>
                <a href="../requests/index.php?student_id=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">View All</a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cog me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="../requests/add.php?student_id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create Request
                    </a>
                    <a href="../documents/upload.php?student_id=<?php echo $id; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-upload me-1"></i>Upload Document
                    </a>
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
