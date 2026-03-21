<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session.php';

$page_title = 'Gallery Management';
$success_messages = [];
$error_messages = [];

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    $target_dir = "../assets/images/gallery/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Count total files
    $total_files = count($_FILES['images']['name']);
    
    // Loop through each file
    for($i = 0; $i < $total_files; $i++) {
        if($_FILES['images']['error'][$i] == 0) {
            $temp_file = $_FILES['images']['tmp_name'][$i];
            $target_file = $target_dir . basename($_FILES['images']['name'][$i]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is a actual image
            $check = getimagesize($temp_file);
            if($check !== false) {
                // Generate unique filename
                $filename = uniqid() . '_' . $i . '.' . $imageFileType;
                $target_file = $target_dir . $filename;
                
                if (move_uploaded_file($temp_file, $target_file)) {
                    $image_path = "assets/images/gallery/" . $filename;
                    $image_title = $total_files == 1 ? $title : $title . ' (' . ($i + 1) . ')';
                    
                    $sql = "INSERT INTO gallery (title, description, image_path) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $image_title, $description, $image_path);
                    
                    if ($stmt->execute()) {
                        $success_messages[] = "Image " . ($i + 1) . " uploaded successfully.";
                    } else {
                        $error_messages[] = "Error uploading image " . ($i + 1) . " to database.";
                    }
                } else {
                    $error_messages[] = "Error uploading file " . ($i + 1) . ".";
                }
            } else {
                $error_messages[] = "File " . ($i + 1) . " is not an image.";
            }
        }
    }
}

// Delete image
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Get image path before deleting
    $sql = "SELECT image_path FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $image_path = '../' . $row['image_path'];
        // Delete file if exists
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete from database
    $sql = "DELETE FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_messages[] = "Image deleted successfully.";
    } else {
        $error_messages[] = "Error deleting image.";
    }
}

// Get all images
$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$result = $conn->query($sql);

// Start output buffering
ob_start();

// Display messages
if (!empty($error_messages)) {
    foreach ($error_messages as $error) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    }
}
if (!empty($success_messages)) {
    foreach ($success_messages as $success) {
        echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

    <style>
        .upload-form-container {
            display: none; /* Initially hidden */
            transition: all 0.3s ease;
        }
        .upload-form-container.show {
            display: block; /* Show when toggled */
        }
    </style>
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
            
                <div class="col d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">Gallery Management</h1>
                    <button class="btn btn-primary" id="toggleUploadForm">
                        <i class="bi bi-plus-circle me-1"></i> Add Image
                    </button>
                </div>
                
                <!-- Upload Form - Initially hidden -->
                <div class="upload-form-container" id="uploadFormContainer">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Upload New Images</h5>
                            <button type="button" class="btn-close" id="closeUploadForm"></button>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data" class="dropzone-form">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <small class="text-muted">For multiple images, numbers will be appended automatically</small>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="images" class="form-label">Images</label>
                                    <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple required>
                                    <div id="preview" class="mt-3 row g-3"></div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-secondary" id="cancelUpload">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cloud-upload"></i> Upload Images
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Gallery List -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Gallery Images</h5>
                        <span class="badge bg-primary"><?php echo $result->num_rows; ?> images</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php while($row = $result->fetch_assoc()): ?>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" 
                                             class="card-img-top" 
                                             alt="<?php echo htmlspecialchars($row['title']); ?>"
                                             style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                            <p class="card-text small text-muted">
                                                <?php echo htmlspecialchars($row['description']); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                                </small>
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-outline-danger btn-sm" 
                                                            onclick="return confirm('Are you sure you want to delete this image?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
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
            const toggleBtn = document.getElementById('toggleUploadForm');
            const uploadForm = document.getElementById('uploadFormContainer');
            const closeBtn = document.getElementById('closeUploadForm');
            const cancelBtn = document.getElementById('cancelUpload');
            
            // form visibility
            toggleBtn.addEventListener('click', function() {
                uploadForm.classList.toggle('show');
            });
            
            // upload-form::Close
            closeBtn.addEventListener('click', function() {
                uploadForm.classList.remove('show');
            });
            
            // button::Cancel
            cancelBtn.addEventListener('click', function() {
                uploadForm.classList.remove('show');
            });

            document.addEventListener('click', function(e) {
                if (!uploadForm.contains(e.target) && e.target !== toggleBtn) {
                    uploadForm.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>