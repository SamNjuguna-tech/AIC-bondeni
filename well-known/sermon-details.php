<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Get sermon ID from URL
$id = $_GET['id'] ?? 0;

// Fetch sermon details
$stmt = $conn->prepare("SELECT * FROM sermons WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$sermon = $result->fetch_assoc();

// Redirect if sermon not found
if (!$sermon) {
    header('Location: sermons.php');
    exit();
}

// Extract video ID from YouTube URL
$video_id = '';
if (!empty($sermon['youtube_url']) && preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $sermon['youtube_url'], $match)) {
    $video_id = $match[1];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sermon['title']); ?> - Our Church</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <style>
        .video-container {
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .sermon-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="sermons.php">Sermons</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($sermon['title']); ?></li>
                    </ol>
                </nav>

                <?php if ($video_id): ?>
                    <div class="video-container mb-4">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>?rel=0&autoplay=1" 
                                    title="<?php echo htmlspecialchars($sermon['title']); ?>"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="sermon-info">
                    <h1 class="mb-3"><?php echo htmlspecialchars($sermon['title']); ?></h1>
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-4">
                            <i class="bi bi-person-circle"></i>
                            <span class="ms-1"><?php echo htmlspecialchars($sermon['speaker']); ?></span>
                        </div>
                        <div>
                            <i class="bi bi-calendar"></i>
                            <span class="ms-1"><?php echo date('F j, Y', strtotime($sermon['date'])); ?></span>
                        </div>
                        <?php if (check_permission('admin')): ?>
                            <div class="ms-auto">
                                <a href="admin/manage_sermons.php?edit=<?php echo $sermon['id']; ?>" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit Sermon
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($sermon['description'])): ?>
                        <div class="sermon-description">
                            <h4 class="mb-3">Description</h4>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($sermon['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Share Buttons -->
                <div class="mt-4">
                    <h4 class="mb-3">Share This Sermon</h4>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-facebook"></i> Share
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($sermon['title']); ?>" 
                           class="btn btn-outline-info" target="_blank">
                            <i class="bi bi-twitter"></i> Tweet
                        </a>
                        <button class="btn btn-outline-secondary" onclick="copyToClipboard(window.location.href)">
                            <i class="bi bi-link-45deg"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Link copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy link:', err);
            });
        }
    </script>
</body>
</html>
