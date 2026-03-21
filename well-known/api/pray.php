<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to pray for requests']);
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

// Verify prayer request exists
$verify_sql = "SELECT id FROM prayer_requests WHERE id = ?";
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

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Prayer request not found']);
    exit;
}

// Check if user has already prayed for this request
$check_sql = "SELECT id FROM prayer_responses WHERE prayer_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
if (!$check_stmt) {
    error_log("MySQL Error: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$check_stmt->bind_param("ii", $prayer_id, $_SESSION['user_id']);
$check_stmt->execute();
$existing = $check_stmt->get_result()->fetch_assoc();

if ($existing) {
    echo json_encode(['success' => false, 'message' => 'You have already prayed for this request']);
    exit;
}

// Add prayer response
$insert_sql = "INSERT INTO prayer_responses (prayer_id, user_id) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
if (!$insert_stmt) {
    error_log("MySQL Error: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$insert_stmt->bind_param("ii", $prayer_id, $_SESSION['user_id']);

if ($insert_stmt->execute()) {
    // Get updated count
    $count_sql = "SELECT COUNT(*) as count FROM prayer_responses WHERE prayer_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $prayer_id);
    $count_stmt->execute();
    $count = $count_stmt->get_result()->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Prayer recorded successfully',
        'count' => $count
    ]);
} else {
    error_log("MySQL Error: " . $insert_stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to record prayer: ' . $insert_stmt->error]);
}
