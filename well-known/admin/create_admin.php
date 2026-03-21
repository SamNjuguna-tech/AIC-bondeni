<?php
require_once __DIR__ . '/../config/database.php';

// Check if admin user exists
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin_count = $result->fetch_assoc()['count'];

if ($admin_count == 0) {
    // Create default admin user
    $username = 'admin';
    $email = 'admin@church.com';
    $password = password_hash('admin321', PASSWORD_DEFAULT);
    $role = 'admin';
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!\n";
        echo "Email: admin@church.com\n";
        echo "Password: admin321\n";
        echo "\nPlease change these credentials immediately after logging in.";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
} else {
    echo "Admin user already exists.";
}
?>
