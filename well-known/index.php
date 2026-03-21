<?php
require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
function get_user_role() {
    return $_SESSION['role'] ?? 'guest';
}

function check_permission($required_role) {
    $role_hierarchy = [
        'guest' => 0,
        'member' => 1,
        'volunteer' => 2,
        'church_leader' => 3,
        'admin' => 4
    ];
    
    $user_role = get_user_role();
    return $role_hierarchy[$user_role] >= $role_hierarchy[$required_role];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Church</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <style>
        #ministries-carousel {
            position: relative;
            height: 400px;
            overflow: hidden;
        }
        .ministry-slide {
            position: absolute;
            width: 100%;
            height: 200px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 1s ease-in-out;
        }
        .ministry-slide.active {
            top: 0;
            z-index: 2;
        }
        .ministry-slide.next {
            top: 200px;
            z-index: 1;
        }
        .ministry-meta {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .ministry-meta i {
            margin-right: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="display-4 my-5">We're Glad You're Here</h1>
            <p class="lead">Grow with us in faith, fellowship, and service</p>
            <a href="about.php" class="btn btn-primary me-2">Learn More</a>
            <a href="sermons.php" class="btn btn-outline-primary">Watch Sermons</a>
        </div>
    </div>

    <!-- Featured Sections -->
    <div class="container py-1">
        <div class="row">
            <!-- Main Content (Events) -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Events</h5>
                        <?php
                            $stmt = $conn->prepare("SELECT title, date, time FROM events WHERE date >= CURDATE() ORDER BY date, time LIMIT 5");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                while ($event = $result->fetch_assoc()) {
                                    echo '<div class="prayer-item mb-3">';
                                    echo '<h6>' . htmlspecialchars($event['title']) . '</h6>';
                                    echo '<small class="text-muted">' . date('M j, Y', strtotime($event['date'])) . ' at ' . date('g:i A', strtotime($event['time'])) . '</small>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>No upcoming events</p>';
                            }
                        ?>
                        <a href="events.php" class="btn btn-sm btn-outline-primary mt-3">View All Events</a>
                    </div>
                </div>
            </div>

            <!-- Sermons Section -->
            <div class="col-md-4">
    <div class="card h-100">
        <div class="card-body">
            <h5 class="card-title">Latest Sermons</h5>
            <?php
                $stmt = $conn->prepare("SELECT id, title, speaker, date, youtube_url FROM sermons ORDER BY date DESC LIMIT 3");
                $stmt->execute();
                $result = $stmt->get_result();
                $counter = 0;
                
                if ($result->num_rows > 0) {
                    while ($sermon = $result->fetch_assoc()) {
                        $video_id = '';
                        if (!empty($sermon['youtube_url']) && preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $sermon['youtube_url'], $match)) {
                            $video_id = $match[1];
                        }

                        echo '<div class="sermon-item mb-4">';
                        if ($video_id && $counter === 0) { 
                            echo '<div class="ratio ratio-16x9 mb-3">';
                            echo '<iframe src="https://www.youtube.com/embed/' . htmlspecialchars($video_id) . '?rel=0" title="' . htmlspecialchars($sermon['title']) . '" allowfullscreen></iframe>';
                            echo '</div>';
                        }
                        echo '<h6>' . htmlspecialchars($sermon['title']) . '</h6>';
                        echo '<p class="text-muted mb-2">By ' . htmlspecialchars($sermon['speaker']) . '<br>';
                        echo date('M j, Y', strtotime($sermon['date'])) . '</p>';
                        echo '<a href="sermon-details.php?id=' . $sermon['id'] . '" class="btn btn-sm btn-outline-primary">Watch Full Sermon</a>';
                        echo '</div>';
                        
                        $counter++;
                        
                        if ($sermon !== $result->fetch_assoc()) {
                            echo '<hr class="my-3">';
                        }
                    }
                } else {
                    echo '<p>No sermons available</p>';
                }
            ?>
            <a href="sermons.php" class="btn btn-primary w-100 mt-3">View More Sermons</a>
        </div>
    </div>
</div>

            <!-- Ministries Section -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Our Ministries</h5>
                        <div id="ministries-carousel" style="height: 400px; overflow: hidden; position: relative;">
                            <div class="ministries-container" style="position: absolute; width: 100%;">
                                <?php
                                function renderMinistry($ministry, $index = 0) {
                                    $isFeatured = $index === 0;
                                    $html = '';
                                    
                                    if (!empty($ministry['image_url'])) {
                                        $imgClass = $isFeatured ? 'mb-3' : 'mb-2';
                                        $html .= '<img src="' . htmlspecialchars($ministry['image_url']) . '" 
                                                class="img-fluid rounded '.$imgClass.'" 
                                                alt="' . htmlspecialchars($ministry['name']) . '" 
                                                style="max-height: '.($isFeatured ? '150px' : '100px').';">';
                                    }
                                    
                                    $html .= '<h6>' . htmlspecialchars($ministry['name']) . '</h6>';
                                    
                                    $desc = $isFeatured ? 
                                        (strlen($ministry['description']) > 100 ? substr($ministry['description'], 0, 100).'...' : $ministry['description']) :
                                        (strlen($ministry['description']) > 30 ? substr($ministry['description'], 0, 30).'...' : $ministry['description']);
                                    $html .= '<p class="small text-muted mb-1">' . htmlspecialchars($desc) . '</p>';
                                    
                                    if (!empty($ministry['meeting_time']) || !empty($ministry['location'])) {
                                        $html .= '<div class="ministry-meta small">';
                                        if (!empty($ministry['meeting_time'])) {
                                            $html .= '<span class="me-2"><i class="bi bi-clock"></i> ' . htmlspecialchars($ministry['meeting_time']) . '</span>';
                                        }
                                        if (!empty($ministry['location'])) {
                                            $html .= '<span><i class="bi bi-geo-alt"></i> ' . htmlspecialchars($ministry['location']) . '</span>';
                                        }
                                        $html .= '</div>';
                                    }
                                    
                                    if ($isFeatured) {
                                        $html .= '<button class="btn btn-sm btn-outline-primary mt-2 learn-more" data-id="'.$ministry['id'].'">Learn More</button>';
                                    }
                                    
                                    return $html;
                                }

                                $stmt = $conn->prepare("SELECT id, name, description, leader, meeting_time, location, image_url 
                                                    FROM ministries 
                                                    WHERE is_active = 1 
                                                    ORDER BY created_at DESC 
                                                    LIMIT 10");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $ministries = $result->fetch_all(MYSQLI_ASSOC);
                                
                                if (count($ministries) > 0) {
                                    $loopedMinistries = array_merge($ministries, $ministries);
                                    foreach ($loopedMinistries as $index => $ministry) {
                                        echo '<div class="ministry-item" style="margin-bottom: 20px; height: 200px;">';
                                        echo renderMinistry($ministry, $index === 0);
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<p>No active ministries at this time</p>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="ministries.php" class="btn btn-outline-primary btn-sm">View All Ministries</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prayers Section -->
        <div class="col pt-3">
            <div class="col-12 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pen-fill me-2"></i>Prayer Requests
                            </h5>
                            <div class="badge bg-white text-primary rounded-pill">
                                <?php if (is_logged_in()): ?>
                                    <a href="prayer.php#submit" class="text-decoration-none">
                                        <i class="bi bi-plus-circle me-1"></i>Submit
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="text-decoration-none">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!is_logged_in()): ?>
                            <div class="alert alert-info m-3 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <div>Please <a href="login.php" class="alert-link">login</a> to view and share prayer requests</div>
                            </div>
                        <?php else: ?>
                            <?php
                            $stmt = $conn->prepare("SELECT pr.*, COUNT(pp.id) as prayer_count 
                                                FROM prayer_requests pr 
                                                LEFT JOIN prayer_participants pp ON pr.id = pp.prayer_request_id 
                                                WHERE pr.is_private = 0 
                                                GROUP BY pr.id 
                                                ORDER BY pr.created_at DESC 
                                                LIMIT 3");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php $prayers = $result->fetch_all(MYSQLI_ASSOC); ?>
                                    <?php foreach ($prayers as $index => $prayer): ?>
                                        <?php
                                        $prayer_id = $prayer['id'];
                                        $praying_count = 0;
                                        
                                        // Try to get prayer count from prayer_responses table
                                        $praying_count_sql = "SELECT COUNT(*) as count FROM prayer_responses WHERE prayer_id = ?";
                                        if ($stmt = $conn->prepare($praying_count_sql)) {
                                            $stmt->bind_param("i", $prayer_id);
                                            $stmt->execute();
                                            $praying_count = $stmt->get_result()->fetch_assoc()['count'];
                                        }
                                        ?>
                                        <div class="list-group-item border-0 px-4 py-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="me-3 flex-grow-1">
                                                    <p class="mb-2 text-dark"><?= htmlspecialchars($prayer['request_text']) ?></p>
                                                    <div class="d-flex justify-content-center text-muted small">
                                                        <span class="mx-1">
                                                            <i class="bi bi-people-fill me-1"></i>
                                                            <?= (int)$praying_count ?> praying
                                                        </span>
                                                        <span class="mx-1">•</span>
                                                        <span class="mx-1">
                                                            <i class="bi bi-clock me-1"></i>
                                                            <?= time_elapsed_string($prayer['created_at']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-heart me-1"></i> Pray
                                                </button> -->
                                            </div>
                                        </div>
                                        <?php if ($index < count($prayers) - 1): ?>
                                            <hr class="m-0 opacity-25">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
                                    <p class="text-muted mt-3 mb-0">No public prayer requests available</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent border-top py-3">
                        <a href="prayer.php" class="btn btn-outline-primary w-100 rounded-pill">
                            <i class="bi bi-list-ul me-1"></i> View All Prayer Requests
                        </a>
                    </div>
                </div>
            </div>

            <?php
function time_elapsed_string($datetime, $full = false) {
    // Get current time and database time in UTC for accurate comparison
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $ago = new DateTime($datetime, new DateTimeZone('UTC'));
    $diff = $now->diff($ago);

    // First check if the post was made less than 60 seconds ago
    $current_time = $now->getTimestamp();
    $post_time = $ago->getTimestamp();
    
    if ($current_time - $post_time < 60) {
        return 'just now';
    }

    // Calculate time units if older than 60 seconds
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
        </div>
    </div>
    
<!-- Gallery Section -->
<section class="py-1 bg-light position-relative overflow-hidden">
    <div class="container position-relative">
        <div class="row justify-content-center mb-4 mb-lg-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Gallery</h2>
                <p class="lead text-muted mb-0">Capturing moments of worship, fellowship, and community</p>
            </div>
        </div>
        
        <!-- Gallery Carousel -->
        <div class="gallery-carousel position-relative mb-4 px-lg-5">
            <div class="position-absolute top-0 bottom-0 start-0 w-25 z-2" style="background: linear-gradient(90deg, var(--bs-light) 0%, transparent 100%);"></div>
            <div class="position-absolute top-0 bottom-0 end-0 w-25 z-2" style="background: linear-gradient(270deg, var(--bs-light) 0%, transparent 100%);"></div>
            
            <div class="gallery-track d-flex gap-3">
                <?php
                $gallery_sql = "SELECT * FROM gallery ORDER BY created_at DESC LIMIT 12";
                $gallery_result = $conn->query($gallery_sql);
                $images = [];
                while($image = $gallery_result->fetch_assoc()) {
                    $images[] = $image;
                }
                
                // Output images twice to create seamless loop
                foreach(array_merge($images, $images) as $image): ?>
                    <div class="gallery-item position-relative rounded-3 overflow-hidden shadow-sm" style="flex: 0 0 280px;">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             class="w-100 h-100 object-fit-cover"
                             alt="<?php echo htmlspecialchars($image['title']); ?>"
                             data-bs-toggle="modal"
                             data-bs-target="#galleryModal"
                             data-title="<?php echo htmlspecialchars($image['title']); ?>"
                             data-description="<?php echo htmlspecialchars($image['description']); ?>"
                             data-image="<?php echo htmlspecialchars($image['image_path']); ?>">
                        <div class="gallery-caption position-absolute start-0 end-0 bottom-0 p-3 bg-dark bg-opacity-75 text-white">
                            <h5 class="mb-1"><?php echo htmlspecialchars($image['title']); ?></h5>
                            <p class="small mb-0 opacity-75"><?php echo htmlspecialchars($image['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="gallery.php" class="btn btn-primary btn-lg px-4 py-2">
                <i class="bi bi-images me-2"></i>View Full Gallery
            </a>
        </div>
    </div>
</section>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3 bg-white bg-opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="" class="img-fluid rounded-top" id="modalImage" alt="">
                <div class="p-4">
                    <h4 class="mb-2" id="modalTitle"></h4>
                    <p class="text-muted mb-0" id="modalDescription"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const galleryModal = document.getElementById('galleryModal');
        
        if (galleryModal) {
            const bsModal = new bootstrap.Modal(galleryModal);
            const modalImage = document.getElementById('modalImage');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');

            // Event delegation
            document.addEventListener('click', function(e) {
                if (e.target.closest('.gallery-item img')) {
                    const img = e.target;
                    modalImage.src = img.dataset.image;
                    modalImage.alt = img.alt || img.dataset.title;
                    modalTitle.textContent = img.dataset.title;
                    modalDescription.textContent = img.dataset.description;
                    bsModal.show();
                }
            });

            // Alternative approach using Bootstrap's modal event
            galleryModal.addEventListener('show.bs.modal', function(event) {
                const img = event.relatedTarget;
                if (img) {
                    modalImage.src = img.dataset.image;
                    modalImage.alt = img.alt || img.dataset.title;
                    modalTitle.textContent = img.dataset.title;
                    modalDescription.textContent = img.dataset.description;
                }
            });
        }
    });
</script>
    
    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img src="" class="img-fluid w-100" alt="">
                    <p class="mt-3 text-muted description"></p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
