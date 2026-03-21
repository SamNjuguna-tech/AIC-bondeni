<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Verify role
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// if (!$user || $user['role'] !== 'admin') {
//     header("Location: unauthorized.php");
//     exit();
// }

// Get admin data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Initialize messages
$error = $success = $password_error = $password_success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        if (empty($username) || empty($email)) {
            $error = "Username and email are required";
        } else {
            // Check if email exists for another user
            $query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $email, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email is already taken by another account";
            } else {
                $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssi", $username, $email, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $success = "Profile updated successfully!";
                    // Refresh admin data
                    $query = "SELECT * FROM users WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $admin = $result->fetch_assoc();
                } else {
                    $error = "Failed to update profile";
                }
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $admin['password'])) {
            $password_error = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $password_error = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $password_error = "Password must be at least 8 characters";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $password_success = "Password changed successfully!";
            } else {
                $password_error = "Failed to change password";
            }
        }
    }
}

// Get notifications
$notifications = [];

// 1. Pending user approvals
$query = "SELECT COUNT(*) as count FROM users WHERE status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$pending_users = $result->fetch_assoc();
if ($pending_users['count'] > 0) {
    $notifications[] = [
        'type' => 'user_approval',
        'count' => $pending_users['count'],
        'message' => $pending_users['count'] . " new user(s) awaiting approval",
        'link' => 'manage_users.php',
        'icon' => 'bi-people-fill',
        'color' => 'primary'
    ];
}

// 2. Pending family join requests
$query = "SELECT COUNT(*) as count FROM family_join_requests WHERE status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$pending_family = $result->fetch_assoc();
if ($pending_family['count'] > 0) {
    $notifications[] = [
        'type' => 'family_request',
        'count' => $pending_family['count'],
        'message' => $pending_family['count'] . " family join request(s) pending",
        'link' => 'manage_join_requests.php',
        'icon' => 'bi-house-door-fill',
        'color' => 'warning'
    ];
}

// 3. New prayer requests (last 7 days)
$query = "SELECT COUNT(*) as count FROM prayer_requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$new_prayers = $result->fetch_assoc();
if ($new_prayers['count'] > 0) {
    $notifications[] = [
        'type' => 'prayer_request',
        'count' => $new_prayers['count'],
        'message' => $new_prayers['count'] . " new prayer request(s) this week",
        'link' => 'manage_prayers.php',
        'icon' => 'bi-chat-square-heart-fill',
        'color' => 'info'
    ];
}

// 4. Upcoming events (next 7 days)
$query = "SELECT COUNT(*) as count FROM events WHERE date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_events = $result->fetch_assoc();
if ($upcoming_events['count'] > 0) {
    $notifications[] = [
        'type' => 'upcoming_event',
        'count' => $upcoming_events['count'],
        'message' => $upcoming_events['count'] . " upcoming event(s) this week",
        'link' => 'manage_events.php',
        'icon' => 'bi-calendar-event-fill',
        'color' => 'success'
    ];
}

// 5. Recent donations (last 7 days)
$query = "SELECT COUNT(*) as count FROM donations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$recent_donations = $result->fetch_assoc();
if ($recent_donations['count'] > 0) {
    $notifications[] = [
        'type' => 'recent_donation',
        'count' => $recent_donations['count'],
        'message' => $recent_donations['count'] . " donation(s) received this week",
        'link' => 'donations.php',
        'icon' => 'bi-cash-stack',
        'color' => 'danger'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css/animate.min.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

    <style>
        .card-header {
            background-color: #1F1F1F;
            border-bottom: 2px solid #3A3A3A;
        }

        .alert {
            border-radius: 10px;
        }

        .form-control:focus {
            border-color: #6200EA;
            box-shadow: 0 0 0 0.2rem rgba(98, 0, 234, 0.25);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
        }
        .notification-item {
            border-left: 4px solid;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .notification-item:hover {
            transform: translateX(5px);
        }
        .notification-primary {
            border-left-color: #0d6efd;
        }
        .notification-warning {
            border-left-color: #ffc107;
        }
        .notification-info {
            border-left-color: #0dcaf0;
        }
        .notification-success {
            border-left-color: #198754;
        }
        .notification-danger {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 bg-dark border-bottom">
                <h1 class="h2 text-light"><i class="fas fa-tachometer-alt me-2"></i> Account</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-light">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-house me-1"></i> Home
                            </a>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="button_logout">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-door-open me-1"></i> Logout
                            </a>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notification Panel -->
            <div class="col-lg-4 mb-4">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-header text-white">
                        <h5><i class="bi bi-bell-fill me-2"></i> Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                                <p class="mt-2">No new notifications</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($notifications as $notification): ?>
                                    <a href="<?php echo $notification['link']; ?>" 
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center notification-item notification-<?php echo $notification['color']; ?>">
                                        <div>
                                            <i class="bi <?php echo $notification['icon']; ?> me-2 text-<?php echo $notification['color']; ?>"></i>
                                            <?php echo $notification['message']; ?>
                                        </div>
                                        <span class="badge bg-<?php echo $notification['color']; ?> rounded-pill"><?php echo $notification['count']; ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profile Update Section -->
            <div class="col-lg-4 mb-4">
                <div class="card mb-4 animate__animated animate__fadeIn">
                    <div class="card-header text-white">
                        <h5><i class="bi bi-person-circle me-2"></i> Update Profile</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Role</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['role']); ?>" readonly>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="col-lg-4 mb-4">
                <div class="card mb-4 animate__animated animate__fadeIn">
                    <div class="card-header text-white">
                        <h5><i class="bi bi-lock me-2"></i> Change Password</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($password_success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $password_success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($password_error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $password_error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/index.js"></script>
</body>
</html>