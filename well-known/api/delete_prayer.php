<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to delete prayer requests']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
if (!$json) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$data = json_decode($json, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$prayer_id = $data['prayer_id'] ?? null;

if (!$prayer_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Prayer ID is required']);
    exit;
}

// Verify prayer request exists and user has permission to delete it
$verify_sql = "SELECT user_id FROM prayer_requests WHERE id = ?";
$verify_stmt = $conn->prepare($verify_sql);
if (!$verify_stmt) {
    error_log("MySQL Error: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$verify_stmt->bind_param("i", $prayer_id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();
$prayer = $result->fetch_assoc();

if (!$prayer) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Prayer request not found']);
    exit;
}

// Check if user has permission to delete
if ($prayer['user_id'] != $_SESSION['user_id'] && !check_permission('church_leader')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this prayer request']);
    exit;
}

// Delete prayer responses first (foreign key constraint)
$delete_responses_sql = "DELETE FROM prayer_responses WHERE prayer_id = ?";
$delete_responses_stmt = $conn->prepare($delete_responses_sql);
if (!$delete_responses_stmt) {
    error_log("MySQL Error: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$delete_responses_stmt->bind_param("i", $prayer_id);
$delete_responses_stmt->execute();

// Delete the prayer request
$delete_prayer_sql = "DELETE FROM prayer_requests WHERE id = ?";
$delete_prayer_stmt = $conn->prepare($delete_prayer_sql);
if (!$delete_prayer_stmt) {
    error_log("MySQL Error: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$delete_prayer_stmt->bind_param("i", $prayer_id);

if ($delete_prayer_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Prayer request deleted successfully'
    ]);
} else {
    error_log("MySQL Error: " . $delete_prayer_stmt->error);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete prayer request'
    ]);
}
