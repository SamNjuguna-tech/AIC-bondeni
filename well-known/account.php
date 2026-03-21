<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error = $success = $password_error = $password_success = $delete_error = $prayer_error = $prayer_success = '';

// Get current user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found");
}

// Check if user is admin
if ($user['role'] === 'admin') {
    header("Location: admin/index.php");
    exit();
}

// Handle account update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Validate inputs
    if (empty($username) || empty($email)) {
        $error = "Username and email are required";
    } else {
        // Check if email is already taken by another user
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email is already taken by another account";
        } else {
            // Update user data
            $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $username, $email, $user_id);
            
            if ($stmt->execute()) {
                $success = "Profile updated successfully!";
                // Refresh user data
                $query = "SELECT * FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error = "Failed to update profile";
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $password_error = "Current password is incorrect";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match";
    } elseif (strlen($new_password) < 8) {
        $password_error = "Password must be at least 8 characters";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $password_success = "Password changed successfully!";
        } else {
            $password_error = "Failed to change password";
        }
    }
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    // Verify password
    $password = $_POST['delete_password'];
    if (password_verify($password, $user['password'])) {
        // Delete user (you might want to soft delete instead)
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            session_destroy();
            header("Location: login.php?account_deleted=1");
            exit();
        } else {
            $delete_error = "Failed to delete account";
        }
    } else {
        $delete_error = "Incorrect password";
    }
}

// Handle prayer request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_prayer'])) {
    $request_text = trim($_POST['prayer_request']);
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    if (empty($request_text)) {
        $prayer_error = "Prayer request cannot be empty";
    } else {
        $query = "INSERT INTO prayer_requests (user_id, request_text, is_private, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $user_id, $request_text, $is_private);
        
        if ($stmt->execute()) {
            $prayer_success = "Prayer request submitted successfully!";
        } else {
            $prayer_error = "Failed to submit prayer request";
        }
    }
}

// Get user's ministries
$ministries = [];
$query = "SELECT m.* FROM ministries m 
          JOIN ministry_members mm ON m.id = mm.ministry_id 
          WHERE mm.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $ministries[] = $row;
}

// Get user's prayer requests
$prayer_requests = [];
$query = "SELECT * FROM prayer_requests WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $prayer_requests[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Church Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <style>
        .profile-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        .ministry-card, .prayer-card {
            margin-bottom: 1rem;
        }
        .private-request {
            color: #6c757d;
            font-style: italic;
        }
        .nav-tabs {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
                
                <!-- Success/Error Alerts -->
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
                
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab">
                            <i class="fas fa-user me-1"></i>Account
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ministries-tab" data-bs-toggle="tab" data-bs-target="#ministries" type="button" role="tab">
                            <i class="fas fa-hands-praying me-1"></i>My Ministries
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prayer-tab" data-bs-toggle="tab" data-bs-target="#prayer" type="button" role="tab">
                            <i class="fas fa-pray me-1"></i>Prayer Requests
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="danger-tab" data-bs-toggle="tab" data-bs-target="#danger" type="button" role="tab">
                            <!-- <i class="fas fa-trash-alt me-2"></i>Delete Account -->
                            <i class="fas fa-user-slash me-2"></i>Delete Account
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="profileTabsContent">
                    <!-- Account Tab -->
                    <div class="tab-pane fade show active" id="account" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h3 class="h5 mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Username</label>
                                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Account Role</label>
                                                <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>" readonly>
                                            </div>
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Update Profile
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h3 class="h5 mb-0"><i class="fas fa-key me-2"></i>Change Password</h3>
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
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            <button type="submit" name="change_password" class="btn btn-warning">
                                                <i class="fas fa-key me-1"></i>Change Password
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ministries Tab -->
                    <div class="tab-pane fade" id="ministries" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-hands-praying me-2"></i>My Ministries</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($ministries)): ?>
                                    <div class="alert alert-info">
                                        You are not currently a member of any ministries.
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($ministries as $ministry): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card ministry-card h-100">
                                                    <?php if (!empty($ministry['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($ministry['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ministry['name']); ?>">
                                                    <?php endif; ?>
                                                    <div class="card-body">
                                                        <h4 class="card-title"><?php echo htmlspecialchars($ministry['name']); ?></h4>
                                                        <p class="card-text"><?php echo htmlspecialchars($ministry['description']); ?></p>
                                                    </div>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item">
                                                            <strong><i class="fas fa-user me-1"></i>Leader:</strong> 
                                                            <?php echo htmlspecialchars($ministry['leader']); ?>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong><i class="fas fa-clock me-1"></i>Meeting Time:</strong> 
                                                            <?php echo htmlspecialchars($ministry['meeting_time']); ?>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <strong><i class="fas fa-map-marker-alt me-1"></i>Location:</strong> 
                                                            <?php echo htmlspecialchars($ministry['location']); ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prayer Requests Tab -->
                    <div class="tab-pane fade" id="prayer" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h3 class="h5 mb-0"><i class="fas fa-praying-hands me-2"></i>Submit Prayer Request</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($prayer_success): ?>
                                            <div class="alert alert-success alert-dismissible fade show">
                                                <?php echo $prayer_success; ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($prayer_error): ?>
                                            <div class="alert alert-danger alert-dismissible fade show">
                                                <?php echo $prayer_error; ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="prayer_request" class="form-label">Your Prayer Request</label>
                                                <textarea class="form-control" id="prayer_request" name="prayer_request" rows="4" required></textarea>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="is_private" name="is_private">
                                                <label class="form-check-label" for="is_private">Keep this request private (only visible to church leaders)</label>
                                            </div>
                                            <button type="submit" name="submit_prayer" class="btn btn-info text-white">
                                                <i class="fas fa-paper-plane me-1"></i>Submit Request
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <h3 class="h5 mb-0"><i class="fas fa-history me-2"></i>My Prayer Requests</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($prayer_requests)): ?>
                                            <div class="alert alert-info">
                                                You haven't submitted any prayer requests yet.
                                            </div>
                                        <?php else: ?>
                                            <div class="list-group">
                                                <?php foreach ($prayer_requests as $request): ?>
                                                    <div class="list-group-item prayer-card">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <p class="mb-1"><?php echo htmlspecialchars($request['request_text']); ?></p>
                                                            <?php if ($request['is_private']): ?>
                                                                <small class="private-request"><i class="fas fa-lock"></i> Private</small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            Submitted on <?php echo date('M j, Y g:i a', strtotime($request['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Danger Zone Tab -->
                    <div class="tab-pane fade" id="danger" role="tabpanel">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h3 class="h5 mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($delete_error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <?php echo $delete_error; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <h4 class="h6 text-danger mb-3">Delete Account</h4>
                                <p class="text-muted">Warning: Deleting your account is permanent and cannot be undone. All your data will be removed from our systems.</p>
                                
                                <form method="POST" onsubmit="return confirm('Are you absolutely sure you want to permanently delete your account? This action cannot be undone.');">
                                    <div class="mb-3">
                                        <label for="delete_password" class="form-label">Enter your password to confirm:</label>
                                        <input type="password" class="form-control" id="delete_password" name="delete_password" required>
                                    </div>
                                    <button type="submit" name="delete_account" class="btn btn-danger">
                                        <i class="fas fa-trash-alt me-1"></i>Delete My Account
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>