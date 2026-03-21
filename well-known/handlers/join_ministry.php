<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Ensure the request is AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Direct access not permitted');
}

// Ensure user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to join a ministry']);
    exit;
}

// Get ministry ID from POST request
$ministry_id = $_POST['ministry_id'] ?? 0;
$action = $_POST['action'] ?? 'join'; // join or leave

if (!$ministry_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ministry ID']);
    exit;
}

// Check if ministry exists and is active
$stmt = $conn->prepare("SELECT id FROM ministries WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $ministry_id);
$stmt->execute();
$ministry = $stmt->get_result()->fetch_assoc();

if (!$ministry) {
    echo json_encode(['success' => false, 'message' => 'Ministry not found or inactive']);
    exit;
}

// Check if user is already a member
$stmt = $conn->prepare("SELECT id FROM ministry_members WHERE ministry_id = ? AND user_id = ?");
$stmt->bind_param("ii", $ministry_id, $_SESSION['user_id']);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($action === 'join') {
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'You are already a member of this ministry']);
        exit;
    }

    // Add user to ministry
    $stmt = $conn->prepare("INSERT INTO ministry_members (ministry_id, user_id, role) VALUES (?, ?, 'member')");
    $stmt->bind_param("ii", $ministry_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Successfully joined the ministry!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error joining ministry']);
    }
} else {
    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'You are not a member of this ministry']);
        exit;
    }

    // Remove user from ministry
    $stmt = $conn->prepare("DELETE FROM ministry_members WHERE ministry_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $ministry_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Successfully left the ministry']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error leaving ministry']);
    }
}
?>
