<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session.php';

// echo "User Role: " . ($_SESSION['role'] ?? 'not set');

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

// Get comprehensive system statistics
$stats = [
    'members' => [
        'total' => 0,
        'active' => 0,
        'new_this_month' => 0
    ],
    'prayers' => [
        'total' => 0,
        'private' => 0,
        'pending' => 0
    ],
    'sermons' => [
        'total' => 0,
        'this_year' => 0,
        'with_video' => 0
    ],
    'ministries' => [
        'total' => 0,
        'active' => 0,
        'members' => 0
    ],
    'requests' => [
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0
    ]
];

// Member statistics
$query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 ELSE 0 END) as new_this_month
          FROM users 
          WHERE role != 'admin'";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['members']['total'] = $row['total'];
    $stats['members']['new_this_month'] = $row['new_this_month'];
}

// Prayer request statistics
$query = "SELECT 
            COUNT(*) as total,
            SUM(is_private) as private,
            SUM(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent
          FROM prayer_requests";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['prayers']['total'] = $row['total'];
    $stats['prayers']['private'] = $row['private'];
    $stats['prayers']['pending'] = $row['recent'];
}

// Sermon statistics
$query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as this_year,
            SUM(CASE WHEN youtube_url IS NOT NULL OR video_url IS NOT NULL THEN 1 ELSE 0 END) as with_video
          FROM sermons";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['sermons']['total'] = $row['total'];
    $stats['sermons']['this_year'] = $row['this_year'];
    $stats['sermons']['with_video'] = $row['with_video'];
}

// Ministry statistics
$query = "SELECT 
            COUNT(*) as total,
            SUM(is_active) as active
          FROM ministries";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['ministries']['total'] = $row['total'];
    $stats['ministries']['active'] = $row['active'];
}

// Get ministry member count
$query = "SELECT COUNT(*) as members FROM ministry_members";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['ministries']['members'] = $row['members'];
}

// Family join request statistics
$query = "SELECT 
            status, COUNT(*) as count 
          FROM family_join_requests 
          GROUP BY status";
$result = $conn->query($query);
while ($result && $row = $result->fetch_assoc()) {
    $stats['requests'][$row['status']] = $row['count'];
}

// Recent activities
$activities = [
    'members' => [],
    'prayers' => [],
    'sermons' => [],
    'ministries' => []
];

// Recent members (last 5)
$query = "SELECT username, email, created_at 
          FROM users 
          WHERE role != 'admin'
          ORDER BY created_at DESC 
          LIMIT 5";
$result = $conn->query($query);
while ($result && $row = $result->fetch_assoc()) {
    $activities['members'][] = $row;
}

// Recent prayer requests (last 5)
$query = "SELECT pr.request_text, u.username, pr.created_at, pr.is_private
          FROM prayer_requests pr
          JOIN users u ON pr.user_id = u.id
          ORDER BY pr.created_at DESC
          LIMIT 5";
$result = $conn->query($query);
while ($result && $row = $result->fetch_assoc()) {
    $activities['prayers'][] = $row;
}

// Recent sermons (last 5)
$query = "SELECT title, speaker, date 
          FROM sermons 
          ORDER BY date DESC 
          LIMIT 5";
$result = $conn->query($query);
while ($result && $row = $result->fetch_assoc()) {
    $activities['sermons'][] = $row;
}

// Recent ministry activities (last 5)
$query = "SELECT m.name, m.leader, COUNT(mm.user_id) as members
          FROM ministries m
          LEFT JOIN ministry_members mm ON m.id = mm.ministry_id
          GROUP BY m.id
          ORDER BY m.created_at DESC
          LIMIT 5";
$result = $conn->query($query);
while ($result && $row = $result->fetch_assoc()) {
    $activities['ministries'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

</head>
<body>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="container-fluid">
        <div class="row">
            <?php include "../includes/admin_left_nav.php" ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-0">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 bg-dark border-bottom">
                <h1 class="h2 text-light"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-light">
                                <a class="nav-link" href="../index.php">
                                    <i class="fas fa-house me-1"></i> Home
                                </a>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info sidebar-toggler" id="sidebarToggle">
                                <i class="fas fa-bars"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="button_logout">
                                <a class="nav-link" href="../logout.php">
                                    <i class="fas fa-door-open me-1"></i> Logout
                                </a>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-5">
                    <!-- Members Card -->
                    <div class="col-xl-2 col-md-2 col-6 mb-4">
                        <div class="card stat-card members h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-0">Total Members</h6>
                                        <h2 class="mb-0"><?php echo $stats['members']['total']; ?></h2>
                                        <p class="text-muted mb-0">
                                            <span class="text-success me-2">+<?php echo $stats['members']['new_this_month']; ?></span>
                                            <span class="text-nowrap">This Month</span>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                </div>
                                <a href="manage_users.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <!-- Ministries Card -->
                    <div class="col-xl-2 col-md-2 col-6 mb-4">
                        <div class="card stat-card ministries h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-0">Ministries</h6>
                                        <h2 class="mb-0"><?php echo $stats['ministries']['total']; ?></h2>
                                        <p class="text-muted mb-0">
                                            <span class="text-success me-2"><?php echo $stats['ministries']['active']; ?></span>
                                            <span class="text-nowrap">Active</span>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hands-praying fa-2x text-warning"></i>
                                    </div>
                                </div>
                                <a href="manage_ministries.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <!-- Sermons Card -->
                    <div class="col-xl-2 col-md-2 col-6 mb-4">
                        <div class="card stat-card sermons h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-0">Sermons</h6>
                                        <h2 class="mb-0"><?php echo $stats['sermons']['total']; ?></h2>
                                        <p class="text-muted mb-0">
                                            <span class="text-success me-2"><?php echo $stats['sermons']['this_year']; ?></span>
                                            <span class="text-nowrap">This Year</span>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-bible fa-2x text-info"></i>
                                    </div>
                                </div>
                                <a href="manage_sermons.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prayer Requests Card -->
                    <div class="col-xl-2 col-md-2 col-6 mb-4">
                        <div class="card stat-card prayers h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-0">Prayers</h6>
                                        <h2 class="mb-0"><?php echo $stats['prayers']['total']; ?></h2>
                                        <p class="text-muted mb-0">
                                            <span class="text-danger me-2"><?php echo $stats['prayers']['private']; ?></span>
                                            <span class="text-nowrap">Private</span>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-pray fa-2x text-success"></i>
                                    </div>
                                </div>
                                <a href="manage_prayers.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <!-- Family Requests Card -->
                    <div class="col-xl-2 col-md-2 col-6 mb-4">
                        <div class="card stat-card requests h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-0">Family Requests</h6>
                                        <h2 class="mb-0"><?php echo $stats['requests']['pending']; ?></h2>
                                        <p class="text-muted mb-0">
                                            <span class="text-nowrap">Pending Review</span>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-plus fa-2x text-danger"></i>
                                    </div>
                                </div>
                                <a href="manage_join_requests.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <!-- Ministry Members Card -->
                    <div class="col-xl-2 col-md-2 col-6 mb-4">
                        <div class="card stat-card ministries h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-uppercase text-muted mb-0">Ministry Members</h6>
                                        <h2 class="mb-0"><?php echo $stats['ministries']['members']; ?></h2>
                                        <p class="text-muted mb-0">
                                            <span class="text-nowrap">Active Participants</span>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-friends fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="sermons-tab" data-bs-toggle="tab" data-bs-target="#sermons" type="button" role="tab">
                            <i class="fas fa-bible me-2"></i>Recent Sermons
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">
                            <i class="fas fa-users me-2"></i>Recent Members
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prayers-tab" data-bs-toggle="tab" data-bs-target="#prayers" type="button" role="tab">
                            <i class="fas fa-pray me-2"></i>Prayer Requests
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ministries-tab" data-bs-toggle="tab" data-bs-target="#ministries" type="button" role="tab">
                            <i class="fas fa-hands-praying me-2"></i>Ministry Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="profile-tab" href="profile.php" role="tab">
                            <i class="fas fa-user-cog me-2"></i>Admin Profile
                        </a>
                    </li>

                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="dashboardTabsContent">
                    <!-- Sermons Tab - Now first and active -->
                    <div class="tab-pane fade show active" id="sermons" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-bible me-2"></i>Recent Sermons</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activities['sermons'])): ?>
                                    <div class="alert alert-info mb-0">No recent sermons found.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Speaker</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($activities['sermons'] as $sermon): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars(substr($sermon['title'], 0, 20)); ?>...</td>
                                                        <td><?php echo htmlspecialchars($sermon['speaker']); ?></td>
                                                        <td><?php echo date('M j', strtotime($sermon['date'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="manage_sermons.php" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-list me-1"></i> View All Sermons
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Members Tab -->
                    <div class="tab-pane fade" id="members" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Members</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activities['members'])): ?>
                                    <div class="alert alert-info mb-0">No recent members found.</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($activities['members'] as $member): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($member['username']); ?></h6>
                                                    <small><?php echo date('M j', strtotime($member['created_at'])); ?></small>
                                                </div>
                                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($member['email']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <a href="manage_users.php" class="btn btn-sm btn-info mt-3">
                                        <i class="fas fa-list me-1"></i> View All Members
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Prayers Tab -->
                    <div class="tab-pane fade" id="prayers" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-pray me-2"></i>Recent Prayer Requests</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activities['prayers'])): ?>
                                    <div class="alert alert-info mb-0">No recent prayer requests found.</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($activities['prayers'] as $prayer): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($prayer['username']); ?></h6>
                                                    <small><?php echo date('M j', strtotime($prayer['created_at'])); ?></small>
                                                </div>
                                                <p class="mb-1"><?php echo htmlspecialchars(substr($prayer['request_text'], 0, 50)); ?>...</p>
                                                <?php if ($prayer['is_private']): ?>
                                                    <small class="text-muted"><i class="fas fa-lock me-1"></i> Private</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <a href="manage_prayers.php" class="btn btn-sm btn-success mt-3">
                                        <i class="fas fa-list me-1"></i> View All Requests
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Ministries Tab -->
                    <div class="tab-pane fade" id="ministries" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-hands-praying me-2"></i>Ministry Overview</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activities['ministries'])): ?>
                                    <div class="alert alert-info mb-0">No ministry data available.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Ministry</th>
                                                    <th>Leader</th>
                                                    <th>Members</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($activities['ministries'] as $ministry): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($ministry['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($ministry['leader']); ?></td>
                                                        <td><?php echo $ministry['members']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="manage_ministries.php" class="btn btn-sm btn-warning mt-2">
                                        <i class="fas fa-list me-1"></i> View All Ministries
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Tab -->
                    <!-- <div class="tab-pane fade" id="profile" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Admin Profile Settings</h5>
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
                                                <input type="text" class="form-control" id="username" name="username" 
                                                    value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email Address</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                    value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Account Role</label>
                                                <input type="text" class="form-control" value="Administrator" readonly>
                                            </div>
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i> Update Profile
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5>
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
                                                <i class="fas fa-key me-2"></i> Change Password
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/index.js"></script>
</body>
</html>