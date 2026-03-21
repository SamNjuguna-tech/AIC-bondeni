<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

try {
    require_once 'config/database.php';
    require_once 'includes/auth.php';
    
    // initialization
    $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?? '';
    $speaker = filter_input(INPUT_GET, 'speaker', FILTER_SANITIZE_STRING) ?? '';
    $sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'newest';
    
    $allowedSorts = ['newest', 'oldest', 'title'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'newest';
    }

    $query = "SELECT * FROM sermons WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $searchParam = "%" . $search . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }

    if (!empty($speaker)) {
        $query .= " AND speaker = ?";
        $params[] = $speaker;
        $types .= "s";
    }

    // sorting
    switch ($sort) {
        case 'oldest':
            $query .= " ORDER BY date ASC";
            break;
        case 'title':
            $query .= " ORDER BY title ASC";
            break;
        default:
            $query .= " ORDER BY date DESC";
    }

    // main query
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception("Failed to get result set: " . $stmt->error);
    }

    $speakerStmt = $conn->prepare("SELECT DISTINCT speaker FROM sermons ORDER BY speaker");
    if ($speakerStmt === false) {
        throw new Exception("Failed to prepare speaker query: " . $conn->error);
    }

    if (!$speakerStmt->execute()) {
        throw new Exception("Speaker query execution failed: " . $speakerStmt->error);
    }

    $speakerResult = $speakerStmt->get_result();
    if ($speakerResult === false) {
        throw new Exception("Failed to get speaker result set: " . $speakerStmt->error);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $errorMessage = "An error occurred while loading sermons. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sermons - Our Church</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php else: ?>
            <div class="row mb-4">
                <div class="col-md-8">
                    <h1>Sermons</h1>
                    <p class="lead">Watch and listen to our latest sermons</p>
                </div>
                <?php if (check_permission('admin')): ?>
                <div class="col-md-4 text-end">
                    <a href="admin/manage_sermons.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Sermon
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Search and Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search sermons...">
                        </div>
                        <div class="col-md-3">
                            <label for="speaker" class="form-label">Speaker</label>
                            <select class="form-select" id="speaker" name="speaker">
                                <option value="">All Speakers</option>
                                <?php while ($speaker_row = $speakerResult->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($speaker_row['speaker']); ?>"
                                            <?php echo $speaker === $speaker_row['speaker'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($speaker_row['speaker']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sermons Grid -->
            <div class="row">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($sermon = $result->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card sermon-card h-100">
                                <?php if (!empty($sermon['youtube_url'])): ?>
                                    <?php
                                        // Extract video ID from YouTube URL
                                        $video_id = '';
                                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $sermon['youtube_url'], $match)) {
                                            $video_id = $match[1];
                                        }
                                    ?>
                                    <?php if ($video_id): ?>
                                        <div class="ratio ratio-16x9">
                                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>?rel=0" 
                                                    title="<?php echo htmlspecialchars($sermon['title']); ?>"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen></iframe>
                                        </div>
                                    <?php else: ?>
                                        <img src="assets/images/sermon-placeholder.jpg" class="card-img-top" alt="Sermon Thumbnail">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <img src="assets/images/sermon-placeholder.jpg" class="card-img-top" alt="Sermon Thumbnail">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($sermon['title']); ?></h5>
                                    <p class="card-text text-muted">
                                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($sermon['speaker']); ?><br>
                                        <i class="bi bi-calendar"></i> <?php echo date('F j, Y', strtotime($sermon['date'])); ?>
                                    </p>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($sermon['description'], 0, 150) . '...')); ?></p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="sermon-details.php?id=<?php echo $sermon['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        Watch Full Sermon
                                    </a>
                                    <?php if (check_permission('admin')): ?>
                                        <a href="admin/manage_sermons.php?edit=<?php echo $sermon['id']; ?>" 
                                           class="btn btn-outline-secondary btn-sm float-end">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No sermons found. <?php echo !empty($search) ? 'Try different search terms.' : ''; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>