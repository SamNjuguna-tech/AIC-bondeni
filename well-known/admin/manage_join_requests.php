<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && isset($_POST['status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE family_join_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $request_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Request status updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating request status.";
    }
    header('Location: manage_join_requests.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Join Requests - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

    <style>
        .request-message {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .request-message:hover {
            white-space: normal;
            overflow: visible;
            cursor: pointer;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
        .action-buttons .btn {
            min-width: 80px;
        }
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom-width: 2px;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
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

            <div class="col">
                <h1>Manage Join Request</h1>
                <!-- <p class="lead">Review and manage ministries</p> -->
            </div>
            
            <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th class="d-none d-sm-table-cell">Contact</th>
                <th class="d-none d-md-table-cell">Message</th>
                <th>Date</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("SELECT * FROM family_join_requests ORDER BY created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($request = $result->fetch_assoc()) {
                    echo '<tr data-status="' . htmlspecialchars($request['status']) . '">';
                    echo '<td>';
                    echo '<div class="fw-semibold">' . htmlspecialchars($request['name']) . '</div>';
                    echo '<div class="text-muted small d-none d-sm-block">' . htmlspecialchars($request['email']) . '</div>';
                    echo '<div class="text-muted small d-sm-none">' . htmlspecialchars($request['phone']) . '</div>';
                    echo '</td>';
                    echo '<td class="d-none d-sm-table-cell">' . htmlspecialchars($request['phone']) . '</td>';
                    echo '<td class="d-none d-md-table-cell request-message" title="' . htmlspecialchars($request['message']) . '">' . htmlspecialchars($request['message']) . '</td>';
                    echo '<td><span class="text-muted">' . date('M j, Y', strtotime($request['created_at'])) . '</span></td>';
                    echo '<td><span class="badge status-badge bg-' . 
                        ($request['status'] === 'approved' ? 'success' : 
                        ($request['status'] === 'rejected' ? 'danger' : 'warning')) . 
                        '">' . ucfirst($request['status']) . '</span></td>';
                    echo '<td class="text-end">';
                    echo '<div class="d-flex flex-column flex-sm-row justify-content-end gap-1">';
                    echo '<form action="manage_join_requests.php" method="post" class="d-inline">';
                    echo '<input type="hidden" name="request_id" value="' . $request['id'] . '">';
                    if ($request['status'] !== 'approved') {
                        echo '<button type="submit" name="status" value="approved" class="btn btn-sm btn-success" title="Approve">';
                        echo '<span class="d-none d-sm-inline">Approve</span>';
                        echo '<span class="d-inline d-sm-none"><i class="fas fa-check"></i></span>';
                        echo '</button>';
                    }
                    echo '</form>';
                    echo '<form action="manage_join_requests.php" method="post" class="d-inline">';
                    echo '<input type="hidden" name="request_id" value="' . $request['id'] . '">';
                    if ($request['status'] !== 'rejected') {
                        echo '<button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger" title="Reject">';
                        echo '<span class="d-none d-sm-inline">Reject</span>';
                        echo '<span class="d-inline d-sm-none"><i class="fas fa-times"></i></span>';
                        echo '</button>';
                    }
                    echo '</form>';
                    echo '</div>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="7" class="empty-state"><i class="fas fa-inbox"></i><h5>No join requests found</h5><p class="text-muted">When new requests come in, they will appear here</p></td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<style>
    @media (max-width: 767.98px) {
        .btn-sm {
            padding: 0.25rem 0.5rem;
            min-width: 32px;
        }
        .request-message {
            max-width: 150px;
        }
        /* .flex-column {
            align-items: flex-end;
        } */
        .flex-column .btn {
            width: 32px;
            text-align: center;
        }
    }

    @media (min-width: 768px) {
        .flex-sm-row {
            flex-direction: row !important;
        }
    }
</style>
                
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
            <!-- </div> -->
        </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/index.js"></script>

</body>
</html>
