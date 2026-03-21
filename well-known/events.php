<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Initialize variables
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT * FROM events WHERE 1=1";
$params = [];
$types = '';

// Apply filters
switch ($filter) {
    case 'week':
        $query .= " AND date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $query .= " AND date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
        break;
    case 'worship':
        $query .= " AND category = 'Worship'";
        break;
    case 'bible':
        $query .= " AND category = 'Bible Study'";
        break;
    case 'community':
        $query .= " AND category = 'Community'";
        break;
    default:
        // 'all' filter - no additional conditions
        break;
}

// Apply search if provided
if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 3, $searchTerm);
    $types = str_repeat('s', count($params));
}

// Add sorting
$query .= " ORDER BY date, time";

// Fetch events
$events = [];
try {
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $events = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "Error fetching events: " . $conn->error;
    }
} catch (Exception $e) {
    $error = "Error fetching events: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events - AIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <style>
:root {
    --dark-blue: #1a237e;
    --dark-blue-hover: #283593;
}

.navbar {
    background-color: var(--dark-blue) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 10%;
    z-index: 111;
}

.navbar-nav .nav-link {
    position: relative;
    padding: 0.5rem 1rem;
    transition: color 0.3s ease;
}

.navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 50%;
    background-color: white;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.navbar-nav .nav-link:hover::after,
.navbar-nav .nav-link.active::after {
    width: 80%;
}
        
.navbar-dark .navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.9);
}
        
.navbar-dark .navbar-nav .nav-link:hover {
    color: #fff;
}
        
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/events.jpg');
    background-size: cover;
    background-position: center;
}
        
.event-card {
    transition: transform 0.3s ease;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
        
.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
        
.event-date {
    background-color: blue;
}
        
.event-time {
    color: var(--primary-color);
    font-weight: bold;
}
        
.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
        
.btn-outline-primary {
    color: var(--primary-color);
    border-color: var(--primary-color);
}
        
.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: blue;
}

footer {
    background-color: var(--dark-blue) !important;
    box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
    color: white;
    padding: 1rem;
    text-align: center;
    font-size: 0.875rem;
    position: relative;
}

/* Active filter button style */
.btn-group .btn.active {
    background-color: var(--dark-blue);
    color: white;
    border-color: var(--dark-blue);
}
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section text-white py-5 mb-4">
        <div class="container py-4 text-center">
            <h1 class="display-4 fw-bold animate__animated animate__fadeIn">Upcoming Events</h1>
            <p class="lead animate__animated animate__fadeIn animate__delay-1s">Join us for worship, fellowship, and service</p>
            <?php if (check_permission('admin')): ?>
                    <li class="page-item btn btn-primary">
                        <a class="page-link" href="admin/manage_events.php">Add Event</a>
                    </li>
                <?php endif; ?>
        </div>
    </section>

    <div class="container mb-5">
        <!-- Error Alert -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filters & Search -->
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="d-flex flex-wrap align-items-center">
                    <h5 class="me-3 mb-2 mb-md-0">Filter by:</h5>
                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                        <a href="?filter=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?filter=week<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $filter === 'week' ? 'active' : ''; ?>">This Week</a>
                        <a href="?filter=month<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn btn-outline-primary <?php echo $filter === 'month' ? 'active' : ''; ?>">This Month</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <form method="get" action="">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search events..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Events Listing -->
        <div class="row g-4">
            <?php if (empty($events)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">
                        <h4 class="alert-heading">No Events Found</h4>
                        <p>Try adjusting your filters or search terms.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($events as $index => $event): 
                    $eventDate = new DateTime($event['date']);
                    $eventTime = new DateTime($event['time']);
                ?>
                    <div class="col-lg-6">
                        <div class="card event-card h-100 animate__animated animate__fadeInUp <?php echo $index > 0 ? 'animate__delay-' . ($index % 3) . 's' : ''; ?>">
                            <div class="row g-0 h-100">
                                <div class="col-md-3 d-flex align-items-stretch">
                                    <div class="event-date w-100 d-flex flex-column justify-content-center text-center text-white p-3">
                                        <div class="event-day fs-2 fw-bold"><?php echo $eventDate->format('d'); ?></div>
                                        <div class="event-month text-uppercase"><?php echo $eventDate->format('M'); ?></div>
                                        <div class="event-year small"><?php echo $eventDate->format('Y'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body d-flex flex-column h-100">
                                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($event['description']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="event-time"><i class="bi bi-clock text-primary"></i> <?php echo $eventTime->format('g:i A'); ?></span>
                                                <span class="text-muted ms-3"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                            </div>
                                            <?php if ($event['max_participants'] > 0): ?>
                                                <span class="badge bg-light text-dark rounded-pill">
                                                    <i class="bi bi-people"></i> 0/<?php echo $event['max_participants']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <nav aria-label="Events pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
                <?php if (check_permission('admin')): ?>
                    <li class="page-item">
                        <a class="page-link" href="admin/manage_events.php">Add Event</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side search for better UX (optional)
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.event-card').forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const description = card.querySelector('.card-text').textContent.toLowerCase();
                
                if (title.includes(term) || description.includes(term)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>