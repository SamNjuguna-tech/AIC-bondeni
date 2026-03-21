<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $id = $_POST['prayer_id'] ?? 0;
        
        // Delete prayer responses first (foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM prayer_responses WHERE prayer_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Then delete the prayer request
        $stmt = $conn->prepare("DELETE FROM prayer_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Prayer request deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting prayer request: " . $conn->error;
        }
    }
    
    header('Location: manage_prayers.php');
    exit();
}

// Fetch all prayer requests with user info and prayer count
$stmt = $conn->prepare("
    SELECT pr.*, u.username, 
           COUNT(pp.id) as prayer_count 
    FROM prayer_requests pr 
    LEFT JOIN users u ON pr.user_id = u.id 
    LEFT JOIN prayer_responses pp ON pr.id = pp.prayer_id 
    GROUP BY pr.id 
    ORDER BY pr.created_at DESC");
$stmt->execute();
$prayers = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Prayer Requests - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

    <style>
        .prayer-text {
            max-width: 300px;
            word-wrap: break-word;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .prayer-text {
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    
    <div class="container-fluid">
        <div class="row">
            <?php include "../includes/admin_left_nav.php" ?>
            
            <main class="col-md-12 ms-sm-auto col-lg-10 px-md-4 py-0">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 bg-dark border-bottom">
                <h1 class="h2 text-light"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
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
                
                <div class="container-fluid mt-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                    <h5 class="mb-0">Prayer Requests Management</h5>
                                    <div class="d-flex">
                                        <div class="input-group input-group-sm me-2" style="width: 200px;">
                                            <input type="text" class="form-control" placeholder="Search requests..." id="searchInput">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <?php if (isset($_SESSION['success'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['error'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Request</th>
                                                    <th>Submitted By</th>
                                                    <th>Date</th>
                                                    <th>People Praying</th>
                                                    <th>Visibility</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($prayer = $prayers->fetch_assoc()): ?>
                                                    <tr>
                                                        <td class="prayer-text"><?php echo nl2br(htmlspecialchars($prayer['request_text'])); ?></td>
                                                        <td><?php echo htmlspecialchars($prayer['username']); ?></td>
                                                        <td><?php echo date('M j, Y', strtotime($prayer['created_at'])); ?></td>
                                                        <td><?php echo $prayer['prayer_count']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $prayer['is_private'] ? 'secondary' : 'success'; ?>">
                                                                <?php echo $prayer['is_private'] ? 'Private' : 'Public'; ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <form action="manage_prayers.php" method="post" class="d-inline" 
                                                                onsubmit="return confirm('Are you sure you want to delete this prayer request?');">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="prayer_id" value="<?php echo $prayer['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
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

</body>
</html>