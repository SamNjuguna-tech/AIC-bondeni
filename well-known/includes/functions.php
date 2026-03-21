<?php
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

function validate_date($date) {
    return (DateTime::createFromFormat('Y-m-d', $date) !== false);
}

function validate_time($time) {
    return (DateTime::createFromFormat('H:i', $time) !== false);
}

function format_date($date) {
    return date('M j, Y', strtotime($date));
}

function format_time($time) {
    return date('g:i A', strtotime($time));
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>