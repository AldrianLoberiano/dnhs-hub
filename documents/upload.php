<?php
/**
 * DNHS Hub - Upload Document
 * 
 * Form to upload a student document
 */

require_once __DIR__ . '/../config/config.php';
requireAuth();

$db = getDBConnection();
$studentId = intval($_GET['student_id'] ?? 0);
$errors = [];

// Get students for dropdown
$stmt = $db->query("SELECT id, student_number, first_name, last_name FROM students WHERE is_archived = 0 ORDER BY last_name, first_name");
$students = $stmt->fetchAll();

// Get document types
$stmt = $db->query("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name");
$docTypes = $stmt->fetchAll();

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
    $studentId = intval($_POST['student_id'] ?? 0);
    $docTypeId = intval($_POST['document_type_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    if (!$studentId) $errors[] = 'Please select a student.';
    if (!$docTypeId) $errors[] = 'Please select a document type.';
    
    // File validation
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please select a file to upload.';
    } else {
        $file = $_FILES['document'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Check file type
        if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
            $errors[] = 'Invalid file type. Allowed: PDF, JPG, JPEG, PNG';
        }
        
        // Check file size (10MB max)
        if ($fileSize > MAX_UPLOAD_SIZE) {
            $errors[] = 'File size exceeds maximum limit of 10MB.';
        }
    }
    
    if (empty($errors)) {
        // Check for existing document (same student + type)
        $stmt = $db->prepare("SELECT MAX(version) as max_version FROM student_documents WHERE student_id = ? AND document_type_id = ?");
        $stmt->execute([$studentId, $docTypeId]);
        $result = $stmt->fetch();
        $version = ($result['max_version'] ?? 0) + 1;
        
        // Generate unique filename
        $newFileName = "doc_{$studentId}_{$docTypeId}_v{$version}_" . time() . "." . $fileExt;
        $uploadPath = DOCUMENTS_PATH . "/" . $newFileName;
        
        // Create directory if not exists
        if (!is_dir(DOCUMENTS_PATH)) {
            mkdir(DOCUMENTS_PATH, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $stmt = $db->prepare("
                INSERT INTO student_documents (student_id, document_type_id, file_name, original_name, file_path, file_type, file_size, version, notes, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $studentId, $docTypeId, $newFileName, $fileName, 
                "assets/uploads/documents/$newFileName", $fileExt, $fileSize, 
                $version, $notes ?: null, $_SESSION['user_id']
            ]);
            
            logAudit('Upload Document', 'Student Documents', "Uploaded document for student ID: $studentId");
            
            // Create notification for admin
            $stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin' AND is_active = 1");
            $stmt->execute();
            $admins = $stmt->fetchAll();
            $studentName = '';
            $stmt2 = $db->prepare("SELECT first_name, last_name FROM students WHERE id = ?");
            $stmt2->execute([$studentId]);
            $studentInfo = $stmt2->fetch();
            if ($studentInfo) $studentName = $studentInfo['first_name'] . ' ' . $studentInfo['last_name'];
            foreach ($admins as $admin) {
                createNotification($admin['id'], 'Document Uploaded', "New document uploaded for student: $studentName", 'info', "../documents/index.php?student_id=$studentId");
            }
            
            setFlashMessage('success', 'Document uploaded successfully.');
            redirect(APP_URL . "/documents/index.php?student_id=$studentId");
        } else {
            $errors[] = 'Failed to upload file. Please try again.';
        }
    }
    }
}

$pageTitle = 'Upload Document - DNHS Hub';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h4>Upload Document</h4>
        <small style="color: rgba(255,255,255,0.8);">Upload a new document for a student</small>
    </div>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to List
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle me-2"></i>
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo sanitize($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data">
                <?php generateCSRFToken(); ?>
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-upload me-2"></i>Document Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student *</label>
                            <select class="form-select" name="student_id" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $studentId == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo sanitize($s['last_name'] . ', ' . $s['first_name'] . ' (' . $s['student_number'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Document Type *</label>
                            <select class="form-select" name="document_type_id" required>
                                <option value="">Select Document Type</option>
                                <?php foreach ($docTypes as $dt): ?>
                                <option value="<?php echo $dt['id']; ?>"><?php echo sanitize($dt['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Select File * (PDF, JPG, JPEG, PNG - Max 10MB)</label>
                            <input type="file" class="form-control file-input" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="file-preview mt-2"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes about this document..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i>Upload Document
                </button>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Upload Guidelines
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Supported formats: PDF, JPG, JPEG, PNG</li>
                    <li>Maximum file size: 10MB</li>
                    <li>Documents will be versioned automatically</li>
                    <li>Previous versions are retained</li>
                    <li>Ensure document is clear and legible</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
