<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $errors = [];
    
    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $leader = trim($_POST['leader'] ?? '');
        $meeting_time = trim($_POST['meeting_time'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $image_url = $_POST['old_image'] ?? '';
        
        // Validate required fields
        if (empty($name)) {
            $errors[] = "Ministry name is required";
        }
        
        // Handle file upload if no errors
        if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/ministries/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            $fileType = $_FILES['image']['type'];
            
            if (!array_key_exists($fileType, $allowedTypes)) {
                $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            } else {
                $fileExtension = $allowedTypes[$fileType];
                $fileName = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $errors[] = "Failed to upload image";
                } else {
                    $image_url = 'assets/images/ministries/' . $fileName;
                    
                    // Delete old image if editing
                    if ($action === 'edit' && !empty($_POST['old_image'])) {
                        $oldImagePath = '../' . $_POST['old_image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }
            }
        }
        
        // Proceed with database operation if no errors
        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO ministries (name, description, leader, meeting_time, location, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $name, $description, $leader, $meeting_time, $location, $image_url, $is_active);
            } else {
                $id = (int)($_POST['ministry_id'] ?? 0);
                $stmt = $conn->prepare("UPDATE ministries SET name = ?, description = ?, leader = ?, meeting_time = ?, location = ?, image_url = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssssssii", $name, $description, $leader, $meeting_time, $location, $image_url, $is_active, $id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['success'] = ($action === 'add') ? "Ministry added successfully!" : "Ministry updated successfully!";
            } else {
                $_SESSION['error'] = "Database error: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = implode("<br>", $errors);
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['ministry_id'] ?? 0);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First delete all members associated with this ministry
            $deleteMembers = $conn->prepare("DELETE FROM ministry_members WHERE ministry_id = ?");
            $deleteMembers->bind_param("i", $id);
            $deleteMembers->execute();
            
            // Get image path before deleting ministry
            $stmt = $conn->prepare("SELECT image_url FROM ministries WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $ministry = $result->fetch_assoc();
            
            // Now delete the ministry
            $stmt = $conn->prepare("DELETE FROM ministries WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                // Delete the associated image file if exists
                if (!empty($ministry['image_url'])) {
                    $imagePath = '../' . $ministry['image_url'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $_SESSION['success'] = "Ministry deleted successfully!";
                $conn->commit();
            } else {
                throw new Exception("Error deleting ministry: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    }
    
    header('Location: manage_ministries.php');
    exit();
}

// Get ministry for editing
$edit_ministry = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM ministries WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_ministry = $result->fetch_assoc();
    
    if (!$edit_ministry) {
        $_SESSION['error'] = "Ministry not found";
        header('Location: manage_ministries.php');
        exit();
    }
}

// Fetch all ministries with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$result = $conn->query("SELECT SQL_CALC_FOUND_ROWS * FROM ministries ORDER BY name LIMIT $limit OFFSET $offset");
$totalRows = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$totalPages = max(1, ceil($totalRows / $limit));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ministries - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

    <style>
        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .sticky-top {
            background-color: white;
            z-index: 1;
        }
        @media (max-width: 767.98px) {
            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            .card-header h3 {
                font-size: 1.25rem;
            }
            .preview-image {
                max-width: 100%;
                height: auto;
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

            <div class="col">
                <h1>Manage Ministries</h1>
                <p class="lead">Review and manage ministries</p>
            </div>

            <div class="row g-4">
                <!-- List Column - Full width -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="card-title mb-2 mb-md-0">All Ministries</h3>
                                <div class="d-flex flex-grow-1 flex-md-grow-0 ms-md-2">
                                    <button class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#ministryFormModal">
                                        <i class="bi bi-plus"></i> Add Ministry
                                    </button>
                                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search ministries...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top" style="top: -1px;">
                                        <tr>
                                            <th>Ministry</th>
                                            <th>Leader</th>
                                            <th class="d-none d-sm-table-cell">Meeting Time</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($ministry = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($ministry['image_url'])): ?>
                                                            <div class="flex-shrink-0">
                                                                <img src="../<?php echo htmlspecialchars($ministry['image_url']); ?>" 
                                                                     width="40" height="40" class="rounded-circle object-fit-cover me-2">
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($ministry['name']); ?></h6>
                                                            <small class="text-muted d-block d-sm-none"><?php echo htmlspecialchars($ministry['meeting_time']); ?></small>
                                                            <small class="text-muted"><?php echo htmlspecialchars($ministry['location']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($ministry['leader']); ?></td>
                                                <td class="d-none d-sm-table-cell"><?php echo htmlspecialchars($ministry['meeting_time']); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill bg-<?php echo $ministry['is_active'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $ministry['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="?edit=<?php echo $ministry['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="manage_ministries.php" method="post" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this ministry and all its members?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="ministry_id" value="<?php echo $ministry['id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mt-3">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Ministry Form Modal -->
<div class="modal fade" id="ministryFormModal" tabindex="-1" aria-labelledby="ministryFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="ministryFormModalLabel"><?php echo $edit_ministry ? 'Edit' : 'Add'; ?> Ministry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="manage_ministries.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="<?php echo $edit_ministry ? 'edit' : 'add'; ?>">
                    <?php if ($edit_ministry): ?>
                        <input type="hidden" name="ministry_id" value="<?php echo $edit_ministry['id']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($edit_ministry['image_url']); ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Ministry Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $edit_ministry ? htmlspecialchars($edit_ministry['name']) : ''; ?>" required>
                        <div class="invalid-feedback">Please provide a ministry name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_ministry ? htmlspecialchars($edit_ministry['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="leader" class="form-label">Leader</label>
                            <input type="text" class="form-control" id="leader" name="leader" 
                                   value="<?php echo $edit_ministry ? htmlspecialchars($edit_ministry['leader']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="meeting_time" class="form-label">Meeting Time</label>
                            <input type="text" class="form-control" id="meeting_time" name="meeting_time" 
                                   value="<?php echo $edit_ministry ? htmlspecialchars($edit_ministry['meeting_time']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="<?php echo $edit_ministry ? htmlspecialchars($edit_ministry['location']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Ministry Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if ($edit_ministry && !empty($edit_ministry['image_url'])): ?>
                            <div class="mt-3">
                                <p class="mb-1">Current Image:</p>
                                <img src="../<?php echo htmlspecialchars($edit_ministry['image_url']); ?>" 
                                     class="img-thumbnail preview-image" style="max-height: 150px;">
                            </div>
                        <?php endif; ?>
                        <div id="imagePreview" class="mt-2 text-center"></div>
                    </div>
                    
                    <div class="mb-3 form-switch">
                        <input type="checkbox" class="form-check-input" role="switch" id="is_active" name="is_active" 
                               <?php echo (!$edit_ministry || $edit_ministry['is_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Active Ministry</label>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            <?php echo $edit_ministry ? 'Update' : 'Add'; ?> Ministry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/index.js"></script>

<script>
    // Initialize the modal when in edit mode
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($edit_ministry): ?>
            const modal = new bootstrap.Modal(document.getElementById('ministryFormModal'));
            modal.show();
        <?php endif; ?>
        
        // Image preview functionality
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail preview-image" style="max-height: 150px;">`;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
</body>
</html>