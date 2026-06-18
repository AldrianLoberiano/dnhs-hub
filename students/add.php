<?php
/**
 * DNHS Hub - Add Student
 * 
 * Form to add a new student record
 */

$pageTitle = 'Add Student - DNHS Hub';
require_once __DIR__ . '/../../includes/header.php';

$db = getDBConnection();
$errors = [];
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber = trim($_POST['student_number'] ?? '');
    $lrn = trim($_POST['lrn'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $birthDate = $_POST['birth_date'] ?? '';
    $birthPlace = trim($_POST['birth_place'] ?? '');
    $nationality = trim($_POST['nationality'] ?? 'Filipino');
    $civilStatus = $_POST['civil_status'] ?? 'Single';
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $homeAddress = trim($_POST['home_address'] ?? '');
    $parentName = trim($_POST['parent_name'] ?? '');
    $guardianName = trim($_POST['guardian_name'] ?? '');
    $guardianContact = trim($_POST['guardian_contact'] ?? '');
    $currentStatus = $_POST['current_status'] ?? 'Enrolled';
    $yearGraduated = !empty($_POST['year_graduated']) ? $_POST['year_graduated'] : null;
    $graduationBatch = trim($_POST['graduation_batch'] ?? '');
    $schoolYear = trim($_POST['school_year'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $strand = trim($_POST['strand'] ?? '');
    $enrollmentHistory = trim($_POST['enrollment_history'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    
    // Validation
    if (empty($studentNumber)) $errors[] = 'Student number is required.';
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';
    if (empty($gender)) $errors[] = 'Gender is required.';
    
    // Check unique student number
    if (!empty($studentNumber)) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE student_number = ?");
        $stmt->execute([$studentNumber]);
        if ($stmt->fetch()['count'] > 0) {
            $errors[] = 'Student number already exists.';
        }
    }
    
    // Check unique LRN
    if (!empty($lrn)) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE lrn = ?");
        $stmt->execute([$lrn]);
        if ($stmt->fetch()['count'] > 0) {
            $errors[] = 'LRN already exists.';
        }
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("
            INSERT INTO students (student_number, lrn, first_name, middle_name, last_name, suffix, 
                gender, birth_date, birth_place, nationality, civil_status, contact_number, email, 
                home_address, parent_name, guardian_name, guardian_contact, current_status, 
                year_graduated, graduation_batch, school_year, section, strand, enrollment_history, 
                remarks, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $studentNumber, $lrn ?: null, $firstName, $middleName ?: null, $lastName, $suffix ?: null,
            $gender, $birthDate ?: null, $birthPlace ?: null, $nationality, $civilStatus, 
            $contactNumber ?: null, $email ?: null, $homeAddress ?: null, $parentName ?: null, 
            $guardianName ?: null, $guardianContact ?: null, $currentStatus,
            $yearGraduated, $graduationBatch ?: null, $schoolYear ?: null, $section ?: null, 
            $strand ?: null, $enrollmentHistory ?: null, $remarks ?: null, $_SESSION['user_id']
        ]);
        
        $studentId = $db->lastInsertId();
        logAudit('Create Student', 'Student Records', "Created student: $firstName $lastName ($studentNumber)");
        setFlashMessage('success', 'Student record created successfully.');
        redirect(APP_URL . "/students/view.php?id=$studentId");
    }
}
?>

<div class="page-header">
    <h4><i class="fas fa-user-plus me-2"></i>Add Student</h4>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to List
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        <?php foreach ($errors as $error): ?>
        <li><?php echo sanitize($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" class="needs-validation" novalidate>
    <!-- Personal Information -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user me-2"></i>Personal Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Student Number *</label>
                    <input type="text" class="form-control" name="student_number" value="<?php echo sanitize($studentNumber ?? generateStudentNumber()); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">LRN</label>
                    <input type="text" class="form-control" name="lrn" value="<?php echo sanitize($lrn ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender *</label>
                    <select class="form-select" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($gender ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($gender ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">First Name *</label>
                    <input type="text" class="form-control" name="first_name" value="<?php echo sanitize($firstName ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" value="<?php echo sanitize($middleName ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name *</label>
                    <input type="text" class="form-control" name="last_name" value="<?php echo sanitize($lastName ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Suffix</label>
                    <input type="text" class="form-control" name="suffix" value="<?php echo sanitize($suffix ?? ''); ?>" placeholder="Jr., Sr., III">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Birth Date</label>
                    <input type="date" class="form-control" name="birth_date" value="<?php echo sanitize($birthDate ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Birth Place</label>
                    <input type="text" class="form-control" name="birth_place" value="<?php echo sanitize($birthPlace ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" class="form-control" name="nationality" value="<?php echo sanitize($nationality ?? 'Filipino'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Civil Status</label>
                    <select class="form-select" name="civil_status">
                        <option value="Single" <?php echo ($civilStatus ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo ($civilStatus ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                        <option value="Widowed" <?php echo ($civilStatus ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?php echo ($civilStatus ?? '') === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" value="<?php echo sanitize($contactNumber ?? ''); ?>" placeholder="09XXXXXXXXX">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?php echo sanitize($email ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Home Address</label>
                    <textarea class="form-control" name="home_address" rows="2"><?php echo sanitize($homeAddress ?? ''); ?></textarea>
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
                    <label class="form-label">Parent Name</label>
                    <input type="text" class="form-control" name="parent_name" value="<?php echo sanitize($parentName ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Guardian Name</label>
                    <input type="text" class="form-control" name="guardian_name" value="<?php echo sanitize($guardianName ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Guardian Contact</label>
                    <input type="text" class="form-control" name="guardian_contact" value="<?php echo sanitize($guardianContact ?? ''); ?>">
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
                    <label class="form-label">Current Status</label>
                    <select class="form-select" name="current_status">
                        <option value="Enrolled" <?php echo ($currentStatus ?? '') === 'Enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                        <option value="Graduated" <?php echo ($currentStatus ?? '') === 'Graduated' ? 'selected' : ''; ?>>Graduated</option>
                        <option value="Transferred" <?php echo ($currentStatus ?? '') === 'Transferred' ? 'selected' : ''; ?>>Transferred</option>
                        <option value="Dropped" <?php echo ($currentStatus ?? '') === 'Dropped' ? 'selected' : ''; ?>>Dropped</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">School Year</label>
                    <input type="text" class="form-control" name="school_year" value="<?php echo sanitize($schoolYear ?? ''); ?>" placeholder="2025-2026">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <input type="text" class="form-control" name="section" value="<?php echo sanitize($section ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Strand</label>
                    <input type="text" class="form-control" name="strand" value="<?php echo sanitize($strand ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year Graduated</label>
                    <input type="number" class="form-control" name="year_graduated" value="<?php echo sanitize($yearGraduated ?? ''); ?>" min="1900" max="<?php echo date('Y'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Graduation Batch</label>
                    <input type="text" class="form-control" name="graduation_batch" value="<?php echo sanitize($graduationBatch ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Enrollment History</label>
                    <textarea class="form-control" name="enrollment_history" rows="3"><?php echo sanitize($enrollmentHistory ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks" rows="3"><?php echo sanitize($remarks ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Save Student
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
