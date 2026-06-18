<?php
/**
 * DNHS Hub - Seed Dummy Data
 * 
 * Run this script once to populate the database with test data
 * Access: http://localhost/dnhs-hub/seed_data.php
 * DELETE this file after running!
 */

require_once __DIR__ . '/config/config.php';

$db = getDBConnection();

echo "<h2>DNHS Hub - Seeding Dummy Data</h2>";

// ============================================
// Dummy Students
// ============================================
$students = [
    ['DNHS-2026-0001', '123456789012', 'Juan', 'Dela', 'Cruz', '', 'Male', '2008-05-15', 'Alabel, Sarangani', 'Filipino', 'Single', '09171234567', 'juan.cruz@email.com', 'Purok 1, Dita, Alabel', 'Pedro Cruz', 'Pedro Cruz', '09171234567', 'Enrolled', null, '', '2025-2026', 'Grade 12', 'STEM', '', ''],
    ['DNHS-2026-0002', '123456789013', 'Maria', 'Santos', 'Reyes', '', 'Female', '2008-08-22', 'Alabel, Sarangani', 'Filipino', 'Single', '09181234567', 'maria.reyes@email.com', 'Purok 2, Dita, Alabel', 'Jose Reyes', 'Jose Reyes', '09181234567', 'Enrolled', null, '', '2025-2026', 'Grade 12', 'ABM', '', ''],
    ['DNHS-2026-0003', '123456789014', 'Pedro', 'Garcia', 'Lopez', 'Jr.', 'Male', '2007-03-10', 'Alabel, Sarangani', 'Filipino', 'Single', '09191234567', 'pedro.lopez@email.com', 'Purok 3, Dita, Alabel', 'Pedro Lopez Sr.', 'Pedro Lopez Sr.', '09191234567', 'Graduated', '2025', 'Batch 2025', '', 'Grade 12', 'HUMSS', '', ''],
    ['DNHS-2026-0004', '123456789015', 'Ana', 'Cruz', 'Gonzales', '', 'Female', '2009-01-25', 'Alabel, Sarangani', 'Filipino', 'Single', '09201234567', 'ana.gonzales@email.com', 'Purok 4, Dita, Alabel', 'Ricardo Gonzales', 'Ricardo Gonzales', '09201234567', 'Enrolled', null, '', '2025-2026', 'Grade 11', 'GAS', '', ''],
    ['DNHS-2026-0005', '123456789016', 'Mark', 'Torres', 'Rivera', '', 'Male', '2008-11-30', 'Alabel, Sarangani', 'Filipino', 'Single', '09211234567', 'mark.rivera@email.com', 'Purok 5, Dita, Alabel', 'Roberto Rivera', 'Roberto Rivera', '09211234567', 'Enrolled', null, '', '2025-2026', 'Grade 12', 'TVL', '', ''],
    ['DNHS-2026-0006', '123456789017', 'Rose', 'Lim', 'Aquino', '', 'Female', '2007-07-18', 'Alabel, Sarangani', 'Filipino', 'Single', '09221234567', 'rose.aquino@email.com', 'Purok 6, Dita, Alabel', 'Ramon Aquino', 'Ramon Aquino', '09221234567', 'Transferred', null, '', '2025-2026', 'Grade 12', 'STEM', '', 'Transferred to National High School'],
    ['DNHS-2026-0007', '123456789018', 'Carlo', 'Bautista', 'Mendoza', '', 'Male', '2008-09-05', 'Alabel, Sarangani', 'Filipino', 'Single', '09231234567', 'carlo.mendoza@email.com', 'Purok 7, Dita, Alabel', 'Antonio Mendoza', 'Antonio Mendoza', '09231234567', 'Enrolled', null, '', '2025-2026', 'Grade 11', 'ABM', '', ''],
    ['DNHS-2026-0008', '123456789019', 'Joy', 'Villanueva', 'Fernandez', '', 'Female', '2009-04-12', 'Alabel, Sarangani', 'Filipino', 'Single', '09241234567', 'joy.fernandez@email.com', 'Purok 8, Dita, Alabel', 'Ricardo Fernandez', 'Ricardo Fernandez', '09241234567', 'Enrolled', null, '', '2025-2026', 'Grade 11', 'HUMSS', '', ''],
    ['DNHS-2026-0009', '123456789020', 'Daniel', 'Ramos', 'De Guzman', '', 'Male', '2008-12-20', 'Alabel, Sarangani', 'Filipino', 'Single', '09251234567', 'daniel.deguzman@email.com', 'Purok 9, Dita, Alabel', 'Manuel De Guzman', 'Manuel De Guzman', '09251234567', 'Dropped', null, '', '2025-2026', 'Grade 10', 'N/A', '', 'Dropped due to transfer'],
    ['DNHS-2026-0010', '123456789021', 'Sofia', 'Mora', 'Castillo', '', 'Female', '2007-06-08', 'Alabel, Sarangani', 'Filipino', 'Single', '09261234567', 'sofia.castillo@email.com', 'Purok 10, Dita, Alabel', 'Jose Castillo', 'Jose Castillo', '09261234567', 'Graduated', '2024', 'Batch 2024', '', 'Grade 12', 'STEM', '', ''],
];

$studentCount = 0;
foreach ($students as $s) {
    // Check if student number already exists
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE student_number = ?");
    $stmt->execute([$s[0]]);
    if ($stmt->fetch()['count'] > 0) continue;
    
    $stmt = $db->prepare("
        INSERT INTO students (student_number, lrn, first_name, middle_name, last_name, suffix,
            gender, birth_date, birth_place, nationality, civil_status, contact_number, email,
            home_address, parent_name, guardian_name, guardian_contact, current_status,
            year_graduated, graduation_batch, school_year, section, strand, enrollment_history, remarks, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute($s);
    $studentCount++;
}
echo "<p style='color: green;'>✓ Inserted $studentCount students</p>";

// ============================================
// Dummy Document Requests
// ============================================
$requests = [
    [1, 1, 'Employment requirements', 'Need SF10 for job application', '2026-06-15', 'Approved'],
    [2, 2, 'Transfer to another school', 'Moving to Manila', '2026-06-16', 'Processing'],
    [3, 3, 'College admission', 'Need Form 137 for university', '2026-06-17', 'Ready for Release'],
    [4, 4, 'Scholarship requirement', 'Need Certificate of Enrollment', '2026-06-18', 'Pending'],
    [5, 5, 'Employment', 'Good Moral for work', '2026-06-19', 'Released'],
    [1, 6, 'Further studies', 'Transcript of Records needed', '2026-06-10', 'Released'],
    [7, 7, 'Government ID processing', 'Birth certificate copy', '2026-06-11', 'Pending'],
    [8, 8, 'Transfer requirement', 'Need diploma copy', '2026-06-12', 'Approved'],
    [9, 1, 'Personal records', 'Need SF10 for personal copy', '2026-06-13', 'Cancelled'],
    [10, 2, 'Employment requirements', 'Form 137 for new job', '2026-06-14', 'Processing'],
];

$requestCount = 0;
$trackingBase = 1;
foreach ($requests as $r) {
    $trackingNumber = sprintf("DNHS-2026-%06d", $trackingBase++);
    $stmt = $db->prepare("
        INSERT INTO document_requests (tracking_number, student_id, document_type_id, purpose, remarks, requested_by, date_requested, status)
        VALUES (?, ?, ?, ?, ?, 1, ?, ?)
    ");
    $stmt->execute([$trackingNumber, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5]]);
    $requestId = $db->lastInsertId();
    
    // Add status history
    $stmt = $db->prepare("INSERT INTO request_status_history (request_id, new_status, changed_by, notes) VALUES (?, 'Pending', 1, 'Request created')");
    $stmt->execute([$requestId]);
    
    // Add intermediate status history if not Pending
    if ($r[5] !== 'Pending') {
        $statuses = ['Pending', 'Approved', 'Processing', 'Ready for Release', 'Released'];
        $targetIndex = array_search($r[5], $statuses);
        if ($targetIndex !== false) {
            for ($i = 1; $i <= $targetIndex; $i++) {
                $stmt = $db->prepare("INSERT INTO request_status_history (request_id, old_status, new_status, changed_by, notes) VALUES (?, ?, ?, 1, ?)");
                $stmt->execute([$requestId, $statuses[$i-1], $statuses[$i], 'Status updated']);
            }
        }
    }
    
    $requestCount++;
}
echo "<p style='color: green;'>✓ Inserted $requestCount document requests with status history</p>";

// ============================================
// Dummy Notifications
// ============================================
$notifications = [
    [1, 'New Request', 'New document request DNHS-2026-000001 created for Juan Cruz', 'info'],
    [1, 'Request Approved', 'Request DNHS-2026-000001 has been approved', 'success'],
    [1, 'Document Uploaded', 'New document uploaded for student Maria Reyes', 'info'],
    [1, 'Request Released', 'Request DNHS-2026-000005 has been released', 'success'],
    [2, 'New Request', 'New document request DNHS-2026-000002 created for Maria Reyes', 'info'],
    [2, 'Request Processing', 'Request DNHS-2026-000002 is now being processed', 'warning'],
];

$notifCount = 0;
foreach ($notifications as $n) {
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->execute($n);
    $notifCount++;
}
echo "<p style='color: green;'>✓ Inserted $notifCount notifications</p>";

// ============================================
// Summary
// ============================================
echo "<hr>";
echo "<h3>Summary</h3>";
echo "<ul>";
echo "<li><strong>Students:</strong> $studentCount new records</li>";
echo "<li><strong>Requests:</strong> $requestCount new requests</li>";
echo "<li><strong>Notifications:</strong> $notifCount new notifications</li>";
echo "</ul>";

echo "<br>";
echo "<p><strong>Test Students:</strong></p>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Student #</th><th>Name</th><th>LRN</th><th>Status</th></tr>";
foreach ($students as $s) {
    echo "<tr>";
    echo "<td>{$s[0]}</td>";
    echo "<td>{$s[3]}, {$s[2]} {$s[4]} {$s[5]}</td>";
    echo "<td>{$s[1]}</td>";
    echo "<td>{$s[16]}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br>";
echo "<p style='color: red;'><strong>IMPORTANT: Delete this file after running!</strong></p>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>
