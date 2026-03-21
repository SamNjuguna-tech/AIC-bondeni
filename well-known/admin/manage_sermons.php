<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/session.php';

$totalPages = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $speaker = $_POST['speaker'] ?? '';
        $youtube_url = $_POST['youtube_url'] ?? '';
        $scripture_reference = $_POST['scripture_reference'] ?? '';
        $series = $_POST['series'] ?? '';

        // For add action, we still need the date
        if ($action === 'add') {
            $date = $_POST['date'] ?? '';
            $requiredFields = !empty($title) && !empty($speaker) && !empty($date);
        } else {
            $requiredFields = !empty($title) && !empty($speaker);
        }

        if ($requiredFields) {
            try {
                if ($action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO sermons (title, description, speaker, date, youtube_url, scripture_reference, series) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $title, $description, $speaker, $date, $youtube_url, $scripture_reference, $series);
                } else {
                    $id = $_POST['sermon_id'] ?? 0;
                    $stmt = $conn->prepare("UPDATE sermons SET title = ?, description = ?, speaker = ?, youtube_url = ?, scripture_reference = ?, series = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $title, $description, $speaker, $youtube_url, $scripture_reference, $series, $id);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = ($action === 'add') ? "Sermon added successfully!" : "Sermon updated successfully!";
                } else {
                    $_SESSION['error'] = "Error: " . $conn->error;
                }
            } catch (mysqli_sql_exception $e) {
                $_SESSION['error'] = "Database error: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = ($action === 'add') ? "Title, speaker, and date are required fields!" : "Title and speaker are required fields!";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['sermon_id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM sermons WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Sermon deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting sermon: " . $conn->error;
        }
    }
    
    header('Location: manage_sermons.php');
    exit();
}

// Get sermon for editing
$edit_sermon = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM sermons WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_sermon = $stmt->get_result()->fetch_assoc();
}

// Fetch all sermons
$stmt = $conn->prepare("SELECT * FROM sermons ORDER BY date DESC");
$stmt->execute();
$sermons = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sermons - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

    <style>
        .video-modal .modal-dialog {
            max-width: 800px;
        }
        .video-modal iframe {
            width: 100%;
            height: 450px;
        }
        .play-btn {
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Video Player Modal -->
<div class="modal fade video-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="videoModalLabel">Sermon Video</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="youtubePlayer" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <h1>Manage Sermons</h1>
                <p class="lead">Review and manage sermon recordings</p>
            </div>

            <div class="row g-4">
                <!-- List Column - Full width -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="card-title mb-2 mb-md-0">All Sermons</h3>
                                <div class="d-flex flex-grow-1 flex-md-grow-0 ms-md-2">
                                    <button class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#sermonFormModal">
                                        <i class="bi bi-plus"></i> Add Sermon
                                    </button>
                                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search sermons...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top" style="top: -1px;">
                                        <tr>
                                            <th>Title</th>
                                            <th>Speaker</th>
                                            <th class="d-none d-sm-table-cell">Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($sermon = $sermons->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($sermon['title']); ?></h6>
                                                            <small class="text-muted d-block d-sm-none"><?php echo date('M j, Y', strtotime($sermon['date'])); ?></small>
                                                            <?php if (!empty($sermon['scripture_reference'])): ?>
                                                                <small class="text-muted"><?php echo htmlspecialchars($sermon['scripture_reference']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($sermon['speaker']); ?></td>
                                                <td class="d-none d-sm-table-cell"><?php echo date('M j, Y', strtotime($sermon['date'])); ?></td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <?php if (!empty($sermon['youtube_url'])): ?>
                                                            <button class="btn btn-outline-primary play-btn" 
                                                                    data-video-url="<?php echo htmlspecialchars($sermon['youtube_url']); ?>" 
                                                                    data-title="<?php echo htmlspecialchars($sermon['title']); ?>"
                                                                    title="Play">
                                                                <i class="bi bi-play-circle"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <a href="?edit=<?php echo $sermon['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="manage_sermons.php" method="post" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this sermon?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="sermon_id" value="<?php echo $sermon['id']; ?>">
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

<!-- Sermon Form Modal -->
<div class="modal fade" id="sermonFormModal" tabindex="-1" aria-labelledby="sermonFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="sermonFormModalLabel"><?php echo $edit_sermon ? 'Edit' : 'Add'; ?> Sermon</h5>
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

                <form action="manage_sermons.php" method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="<?php echo $edit_sermon ? 'edit' : 'add'; ?>">
                    <?php if ($edit_sermon): ?>
                        <input type="hidden" name="sermon_id" value="<?php echo $edit_sermon['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Sermon Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo $edit_sermon ? htmlspecialchars($edit_sermon['title']) : ''; ?>" required>
                        <div class="invalid-feedback">Please provide a sermon title.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_sermon ? htmlspecialchars($edit_sermon['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="speaker" class="form-label">Speaker <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="speaker" name="speaker" 
                                   value="<?php echo $edit_sermon ? htmlspecialchars($edit_sermon['speaker']) : ''; ?>" required>
                            <div class="invalid-feedback">Please provide a speaker name.</div>
                        </div>
                        
                        <?php if (!$edit_sermon): ?>
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?php echo $edit_sermon ? htmlspecialchars($edit_sermon['date']) : ''; ?>" required>
                                <div class="invalid-feedback">Please select a date.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="youtube_url" class="form-label">YouTube URL</label>
                        <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                               value="<?php echo $edit_sermon ? htmlspecialchars($edit_sermon['youtube_url']) : ''; ?>"
                               placeholder="https://youtube.com/watch?v=...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="scripture_reference" class="form-label">Scripture Reference</label>
                        <input type="text" class="form-control" id="scripture_reference" name="scripture_reference" 
                               value="<?php echo $edit_sermon ? htmlspecialchars($edit_sermon['scripture_reference']) : ''; ?>"
                               placeholder="e.g., John 3:16">
                    </div>
                    
                    <div class="mb-3">
                        <label for="series" class="form-label">Series</label>
                        <input type="text" class="form-control" id="series" name="series" 
                               value="<?php echo $edit_sermon ? htmlspecialchars($edit_sermon['series']) : ''; ?>"
                               placeholder="e.g., Summer Bible Study">
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary me-md-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            <?php echo $edit_sermon ? 'Update' : 'Add'; ?> Sermon
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
        <?php if ($edit_sermon): ?>
            const modal = new bootstrap.Modal(document.getElementById('sermonFormModal'));
            modal.show();
        <?php endif; ?>

        // Handle video play buttons
        const playButtons = document.querySelectorAll('.play-btn');
        const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
        const youtubePlayer = document.getElementById('youtubePlayer');
        const videoModalTitle = document.getElementById('videoModalLabel');

        playButtons.forEach(button => {
            button.addEventListener('click', function() {
                const videoUrl = this.getAttribute('data-video-url');
                const title = this.getAttribute('data-title');
                
                // Extract video ID from URL (handles various YouTube URL formats)
                let videoId = '';
                if (videoUrl.includes('youtube.com/watch?v=')) {
                    videoId = videoUrl.split('v=')[1].split('&')[0];
                } else if (videoUrl.includes('youtu.be/')) {
                    videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
                }
                
                if (videoId) {
                    youtubePlayer.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
                    videoModalTitle.textContent = title;
                    videoModal.show();
                }
            });
        });

        // reset video 
        document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
            youtubePlayer.src = '';
        });
    });
</script>
</body>
</html>