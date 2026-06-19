<?php
require_once __DIR__ . '/../config/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/requests/index.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Invalid security token.');
    redirect(APP_URL . '/requests/index.php');
}

$db = getDBConnection();
$requestId = intval($_POST['request_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$notes = trim($_POST['notes'] ?? '');
$releasedTo = trim($_POST['released_to'] ?? '');

if (!$requestId || empty($newStatus)) {
    setFlashMessage('error', 'Invalid request data.');
    redirect(APP_URL . '/requests/index.php');
}

$validTransitions = [
    'Pending' => ['Approved', 'Rejected', 'Cancelled'],
    'Approved' => ['Processing', 'Rejected', 'Cancelled'],
    'Processing' => ['Ready for Release', 'Cancelled'],
    'Ready for Release' => ['Released']
];

$stmt = $db->prepare("SELECT * FROM document_requests WHERE id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    setFlashMessage('error', 'Request not found.');
    redirect(APP_URL . '/requests/index.php');
}

$currentStatus = $request['status'];
if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus])) {
    setFlashMessage('error', 'Invalid status transition.');
    redirect(APP_URL . "/requests/view.php?id=$requestId");
}

$updateFields = ['status = ?'];
$updateParams = [$newStatus];

if ($newStatus === 'Released') {
    $updateFields[] = 'actual_release_date = ?';
    $updateParams[] = date('Y-m-d');
    $updateFields[] = 'released_to = ?';
    $updateParams[] = $releasedTo ?: null;
    $updateFields[] = 'released_by = ?';
    $updateParams[] = $_SESSION['user_id'];
}

$updateParams[] = $requestId;
$stmt = $db->prepare("UPDATE document_requests SET " . implode(', ', $updateFields) . " WHERE id = ?");
$stmt->execute($updateParams);

$stmt = $db->prepare("INSERT INTO request_status_history (request_id, old_status, new_status, changed_by, notes) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$requestId, $currentStatus, $newStatus, $_SESSION['user_id'], $notes ?: null]);

logAudit('Update Request Status', 'Document Requests', "Changed request {$request['tracking_number']} from $currentStatus to $newStatus");

if ($request['requested_by']) {
    $notifMessage = "Request {$request['tracking_number']} status updated to: $newStatus";
    createNotification($request['requested_by'], 'Request Updated', $notifMessage, 'info', "../requests/view.php?id=$requestId");
}

setFlashMessage('success', "Request status updated to $newStatus.");
redirect(APP_URL . "/requests/view.php?id=$requestId");
