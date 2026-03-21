<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Define the pages that leaders can potentially access
$leader_pages = [
    'index' => ['label' => 'Dashboard', 'icon' => 'tachometer-alt'],
    'gallery' => ['label' => 'Manage Gallery', 'icon' => 'images'],
    'manage_events' => ['label' => 'Manage Events', 'icon' => 'calendar'],
    'manage_join_requests' => ['label' => 'Manage Join Requests', 'icon' => 'users'],
    'manage_ministries' => ['label' => 'Manage Ministries', 'icon' => 'church'],
    'manage_prayers' => ['label' => 'Manage Prayers', 'icon' => 'pray'],
    'manage_sermons' => ['label' => 'Manage Sermons', 'icon' => 'book-bible'],
    'settings' => ['label' => 'settings', 'icon' => 'gear']
];

// Handle settings update via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_update'])) {
    header('Content-Type: application/json');
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid form submission']);
        exit();
    }

    try {
        // Handle user approval setting if present
        if (isset($_POST['require_approval'])) {
            $require_approval = $_POST['require_approval'] === 'true' ? '1' : '0';
            
            $sql = "INSERT INTO settings (setting_key, setting_value) 
                    VALUES ('require_user_approval', ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $require_approval, $require_approval);
            $stmt->execute();
        }

        // Handle leader permissions if present
        if (isset($_POST['leader_permission'])) {
            $page = $_POST['leader_permission']['page'];
            $allowed = $_POST['leader_permission']['allowed'] === 'true' ? '1' : '0';
            $setting_key = 'leader_access_' . $page;
            
            $sql = "INSERT INTO settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $setting_key, $allowed, $allowed);
            $stmt->execute();
        }

        echo json_encode(['status' => 'success', 'message' => 'Settings updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error updating settings: ' . $e->getMessage()]);
    }
    exit();
}

// Get current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$require_approval = $settings['require_user_approval'] ?? '0';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #3b82f6;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .permission-icon {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            color: #6c757d;
        }
        .permission-item:hover {
            background-color: rgba(0,0,0,0.03);
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include "../includes/admin_left_nav.php" ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 bg-dark border-bottom">
                    <h1 class="h2 text-light"><i class="fas fa-cog me-2"></i>System Settings</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../index.php" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-house me-1"></i> Home
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-info sidebar-toggler" id="sidebarToggle">
                                <i class="fas fa-bars"></i>
                            </button>
                            <a href="../logout.php" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-door-open me-1"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="toast-container">
                    <div class="toast align-items-center text-white bg-success" role="alert" aria-live="assertive" aria-atomic="true" id="saveToast">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-check-circle me-2"></i> Settings saved successfully!
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-shield me-2"></i>User Registration Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="require_approval" 
                                               name="require_approval" 
                                               <?php echo $require_approval == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="require_approval">
                                            Require Admin Approval for New Users
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        When enabled, new users will need administrator approval before they can log in.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-tie me-2"></i>Church Leader Permissions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Page</th>
                                                <th class="text-center">Access</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($leader_pages as $page => $data): 
                                                $allowed = $settings['leader_access_' . $page] ?? '0';
                                            ?>
                                                <tr class="permission-item">
                                                    <td>
                                                        <i class="fas fa-<?php echo $data['icon']; ?> permission-icon"></i>
                                                        <?php echo htmlspecialchars($data['label']); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <label class="switch">
                                                            <input type="checkbox" class="leader-permission" 
                                                                   data-page="<?php echo $page; ?>" 
                                                                   <?php echo $allowed == '1' ? 'checked' : ''; ?>>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/index.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize toast
            const saveToast = new bootstrap.Toast(document.getElementById('saveToast'));
            
            // Handle require approval toggle
            const requireApproval = document.getElementById('require_approval');
            if (requireApproval) {
                requireApproval.addEventListener('change', function() {
                    updateSetting('require_approval', this.checked);
                });
            }
            
            // Handle leader permission toggles
            const permissionToggles = document.querySelectorAll('.leader-permission');
            permissionToggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const page = this.dataset.page;
                    updateLeaderPermission(page, this.checked);
                });
            });
            
            function updateSetting(key, value) {
                const csrfToken = document.getElementById('csrf_token').value;
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax_update=1&csrf_token=${encodeURIComponent(csrfToken)}&${key}=${value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        saveToast.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
            
            function updateLeaderPermission(page, allowed) {
                const csrfToken = document.getElementById('csrf_token').value;
                const data = {
                    leader_permission: {
                        page: page,
                        allowed: allowed
                    }
                };
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax_update=1&csrf_token=${encodeURIComponent(csrfToken)}&leader_permission[page]=${page}&leader_permission[allowed]=${allowed}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        saveToast.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>