<?php
session_start();
// require_once '../config/database.php';

function register_user($username, $email, $password) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "Username or email already exists";
    }

    $require_approval = get_setting('require_user_approval', '0');
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $status = $require_approval == '1' ? 'pending' : 'active';

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $status);

    if ($stmt->execute()) {
        return $status == 'pending'
            ? "Registration successful. Please wait for admin approval to log in."
            : "Registration successful. You can now log in.";
    }

    return "Error registering user";
}

function login_user($email, $password) {
    global $conn;

    $stmt = $conn->prepare("SELECT id, username, email, password, status, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['status'] == 'pending') {
                return "Waiting for admin approval. Once approved you should be able to log in. Thank you for your patience!";
            }

            if ($user['status'] != 'active') {
                return "Your account is not active. Please contact administrator.";
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }

    return "Invalid email or password";
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    return $_SESSION['role'] ?? 'guest';
}

function logout_user() {
    session_destroy();
    session_start();
}

function check_permission($required_role) {
    $role_hierarchy = [
        'guest' => 0,
        'member' => 1,
        'volunteer' => 2,
        'church_leader' => 3,
        'admin' => 4
    ];

    $user_role = get_user_role();
    return isset($role_hierarchy[$user_role]) &&
           isset($role_hierarchy[$required_role]) &&
           $role_hierarchy[$user_role] >= $role_hierarchy[$required_role];
}

function get_setting($key, $default = null) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc()['setting_value'] : $default;
}

function has_permission($permission_key) {
    global $conn;

    if (!is_logged_in()) {
        return false;
    }

    $user_role = get_user_role();

    if ($user_role === 'admin') {
        return true;
    }

    if ($user_role === 'church_leader') {
        $setting_key = 'leader_' . $permission_key;
        return get_setting($setting_key, '0') === '1';
    }

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("ss", $user_role, $permission_key);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 && $result->fetch_assoc()['value'] === '1';
}

// Page Access Map
$restricted_pages = [
    'gallery.php' => 'access_gallery',
    'manage_events.php' => 'access_manage_events',
    'manage_join_requests.php' => 'access_manage_join_requests',
    'manage_ministries.php' => 'access_manage_ministries',
    'manage_prayers.php' => 'access_manage_prayers',
    'manage_sermons.php' => 'access_manage_sermons',
    'index.php' => 'access_index',
    'profile.php' => 'access_profile',
    'settings.php' => 'access_settings',



];

function check_page_access() {
    global $restricted_pages;

    $page_name = basename($_SERVER['PHP_SELF']);

    if (isset($restricted_pages[$page_name])) {
        if (!is_logged_in()) {
            header('Location: ../login.php');
            exit;
        }

        $permission_key = $restricted_pages[$page_name];
        if (!has_permission($permission_key)) {
            http_response_code(403);
           include '../handlers/unauthorize.html';
            exit;
        }
    }
}

// Execute on page load
check_page_access();


// join family check - disable join familiy button
function is_ministry_member($id = null) {
    global $conn;
    if ($id === null) {
        if (!isset($_SESSION['id'])) {
            return false;
        }
        $id = $_SESSION['id'];
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM family_join_requests WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_row()[0] > 0;
}
?>
