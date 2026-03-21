<?php
require_once 'session.php';

// Input/Output Helpers
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function validate_time($time, $format = 'H:i') {
    $t = DateTime::createFromFormat($format, $time);
    return $t && $t->format($format) === $time;
}

// Security Helpers
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

// Settings Helper
function get_setting($key, $default = null) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc()['setting_value'] : $default;
}

// Page Access Control (as you originally had it)
$restricted_pages = [
    'gallery.php' => 'leader_access_gallery',
    'manage_events.php' => 'leader_access_manage_events',
    'manage_join_requests.php' => 'leader_access_manage_join_requests',
    'manage_ministries.php' => 'leader_access_manage_ministries',
    'manage_prayers.php' => 'leader_access_manage_prayers',
    'manage_sermons.php' => 'leader_access_manage_sermons',
    'index.php' => 'leader_access_index',
];

function can_access_page($user_id, $setting_key) {
    global $conn;

    // Get the user's role
    $query = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user['role'] === 'admin') {
        return true; // Admins can access everything
    }

    if ($user['role'] === 'church_leader') {
        $setting_value = get_setting($setting_key);
        return $setting_value == '1';
    }

    return false;
}

// Check access for current page
function check_page_access() {
    global $restricted_pages;
    
    $page_name = basename($_SERVER['PHP_SELF']);
    
    if (isset($restricted_pages[$page_name])) {
        if (!isset($_SESSION['user_id'])) {
            redirect('login.php');
        }
        
        if (!can_access_page($_SESSION['user_id'], $restricted_pages[$page_name])) {
            header('HTTP/1.0 403 Forbidden');
            echo "You do not have permission to access this page.";
            exit;
        }
    }
}
?>