<?php
    require_once '../config/database.php';
    require_once '../includes/auth.php';
    require_once '../includes/functions.php';
    require_once '../includes/session.php';

    // Initialize variables
    $errors = [];
    $success = '';
    $edit_event = null;

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = sanitize_input($_POST['action'] ?? '');
        
        if (!validate_csrf_token($_POST['csrf_token'])) {
            $errors[] = "Invalid CSRF token";
        } else {
            if ($action === 'add' || $action === 'edit') {
                $title = sanitize_input($_POST['title'] ?? '');
                $description = sanitize_input($_POST['description'] ?? '');
                $date = sanitize_input($_POST['date'] ?? '');
                $time = sanitize_input($_POST['time'] ?? '');
                $location = sanitize_input($_POST['location'] ?? '');
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                
                if (empty($title)) {
                    $errors[] = "Event title is required";
                }
                
                if (empty($date) || !validate_date($date)) {
                    $errors[] = "Valid date is required";
                }
                
                if (!empty($time) && !validate_time($time)) {
                    $errors[] = "Invalid time format";
                }
                
                if (empty($errors)) {
                    try {
                        if ($action === 'add') {
                            $stmt = $conn->prepare("INSERT INTO events (title, description, date, time, location, is_featured) 
                                                VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssssi", $title, $description, $date, $time, $location, $is_featured);
                        } else {
                            $id = (int)$_POST['event_id'];
                            $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, date = ?, time = ?, 
                                                location = ?, is_featured = ? WHERE id = ?");
                            $stmt->bind_param("sssssii", $title, $description, $date, $time, $location, $is_featured, $id);
                        }
                        
                        if ($stmt->execute()) {
                            $success = ($action === 'add') ? "Event added successfully!" : "Event updated successfully!";
                        } else {
                            $errors[] = "Database error: " . $conn->error;
                        }
                    } catch (Exception $e) {
                        $errors[] = "Error: " . $e->getMessage();
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)$_POST['event_id'];
                
                try {
                    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        $success = "Event deleted successfully!";
                    } else {
                        $errors[] = "Error deleting event: " . $conn->error;
                    }
                } catch (Exception $e) {
                    $errors[] = "Error: " . $e->getMessage();
                }
            }
        }
        
        // Store messages::SESSION
        if (!empty($success)) {
            $_SESSION['success'] = $success;
        }
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
        }
        redirect('manage_events.php');
    }

    // Get event for editing
    if (isset($_GET['edit'])) {
        $id = (int)$_GET['edit'];
        try {
            $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $edit_event = $result->fetch_assoc();
            
            // Ensure is_featured is set
            if ($edit_event && !isset($edit_event['is_featured'])) {
                $edit_event['is_featured'] = 0;
            }
        } catch (Exception $e) {
            $_SESSION['errors'][] = "Error fetching event: " . $e->getMessage();
        }
    }

    // All events
    $events = [];
    try {
        $stmt = $conn->prepare("SELECT * FROM events ORDER BY date DESC, time ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $events = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        $_SESSION['errors'][] = "Error fetching events: " . $e->getMessage();
    }

    // Retrieve session messages
    $success = $_SESSION['success'] ?? '';
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['success'], $_SESSION['errors']);

    $csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

</head>
<body class="admin-dashboard">
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
                    <h1>Manage Events</h1>
                    <p class="lead">Create and manage church events</p>
                </div>

                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- List Column - Full width -->
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h3 class="card-title mb-2 mb-md-0">All Events</h3>
                                    <div class="d-flex flex-grow-1 flex-md-grow-0 ms-md-2">
                                        <button class="btn btn-sm btn-light me-2" data-bs-toggle="modal" data-bs-target="#eventFormModal">
                                            <i class="bi bi-plus"></i> Add Event
                                        </button>
                                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search events...">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($events)): ?>
                                    <div class="alert alert-info mb-0">No events found.</div>
                                <?php else: ?>
                                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light sticky-top" style="top: -1px;">
                                                <tr>
                                                    <th>Title</th>
                                                    <th class="d-none d-sm-table-cell">Date</th>
                                                    <th>Time</th>
                                                    <th class="d-none d-md-table-cell">Location</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($events as $event): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                                            <small class="text-muted d-block d-sm-none"><?php echo format_date($event['date']); ?></small>
                                                            <?php if (isset($event['is_featured']) && $event['is_featured']): ?>
                                                                <span class="badge bg-warning text-dark ms-2">Featured</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="d-none d-sm-table-cell"><?php echo format_date($event['date']); ?></td>
                                                        <td><?php echo $event['time'] ? format_time($event['time']) : '-'; ?></td>
                                                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($event['location'] ?: '-'); ?></td>
                                                        <td>
                                                            <?php 
                                                            $event_date = new DateTime($event['date']);
                                                            $today = new DateTime();
                                                            if ($event_date < $today) {
                                                                echo '<span class="badge bg-secondary">Past</span>';
                                                            } elseif ($event_date == $today) {
                                                                echo '<span class="badge bg-success">Today</span>';
                                                            } else {
                                                                echo '<span class="badge bg-primary">Upcoming</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="?edit=<?php echo $event['id']; ?>" class="btn btn-outline-primary" 
                                                                   data-bs-toggle="tooltip" title="Edit">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="post" action="manage_events.php" class="d-inline"
                                                                      onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                                                    <button type="submit" class="btn btn-outline-danger" 
                                                                            data-bs-toggle="tooltip" title="Delete">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Event Form Modal -->
    <div class="modal fade" id="eventFormModal" tabindex="-1" aria-labelledby="eventFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="eventFormModalLabel">
                        <i class="bi bi-calendar-event"></i> 
                        <?php echo $edit_event ? 'Edit Event' : 'Add New Event'; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="manage_events.php" id="eventForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="<?php echo $edit_event ? 'edit' : 'add'; ?>">
                        <?php if ($edit_event): ?>
                            <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo $edit_event['title'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_event['description'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?php echo $edit_event['date'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="time" name="time" 
                                       value="<?php echo $edit_event['time'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo $edit_event['location'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                <?php echo (isset($edit_event['is_featured']) && $edit_event['is_featured']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">Featured Event</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="eventForm">
                        <i class="bi bi-save"></i> 
                        <?php echo $edit_event ? 'Update Event' : 'Add Event'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/index.js"></script>

    <script>
        // Initialize the modal when in edit mode
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($edit_event): ?>
                const modal = new bootstrap.Modal(document.getElementById('eventFormModal'));
                modal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>