<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
// require_once 'includes/session.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}


$success_message = '';
$error_message = '';

// Handle new prayer request submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_logged_in()) {
    $request_text = $_POST['request_text'] ?? '';
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    if (!empty($request_text)) {
        $stmt = $conn->prepare("INSERT INTO prayer_requests (user_id, request_text, is_private) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $_SESSION['user_id'], $request_text, $is_private);
        
        if ($stmt->execute()) {
            $success_message = 'Your prayer request has been submitted.';
        } else {
            $error_message = 'Failed to submit prayer request. Please try again.';
        }
    } else {
        $error_message = 'Please enter your prayer request.';
    }
}

// Fetch prayer requests with prayer counts
$query = "SELECT pr.*, u.username, 
          (SELECT COUNT(*) FROM prayer_responses WHERE prayer_id = pr.id) as prayer_count,
          EXISTS(SELECT 1 FROM prayer_responses WHERE prayer_id = pr.id AND user_id = ?) as has_prayed
          FROM prayer_requests pr 
          JOIN users u ON pr.user_id = u.id 
          WHERE pr.is_private = 0 OR pr.user_id = ?
          ORDER BY pr.created_at DESC";
$stmt = $conn->prepare($query);
$user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayer Requests - Our Church</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <h1>Prayer Requests</h1>
                <p class="lead">Share your prayer requests with our church community</p>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Submit Prayer Request Form -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Submit a Prayer Request</h5>
                        <?php if (is_logged_in()): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="request_text" class="form-label">Your Request</label>
                                    <textarea class="form-control" id="request_text" name="request_text" rows="4" required></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_private" name="is_private">
                                    <label class="form-check-label" for="is_private">
                                        Make this request private
                                        <i class="bi bi-question-circle" data-bs-toggle="tooltip" 
                                           title="Private requests are only visible to church leaders"></i>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Request</button>
                            </form>
                        <?php else: ?>
                            <p>Please <a href="login.php">login</a> to submit a prayer request.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (check_permission('church_leader')): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Leader Tools</h5>
                        <a href="admin/prayer-dashboard.php" class="btn btn-outline-primary">
                            <i class="bi bi-speedometer2"></i> Prayer Dashboard
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Prayer Requests List -->
            <div class="col-md-8">
                <div class="prayer-requests">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($request = $result->fetch_assoc()): ?>
                            <div class="card mb-3 prayer-request">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-1">
                                            <?php echo htmlspecialchars($request['username']); ?>
                                            <?php if ($request['is_private']): ?>
                                                <span class="badge bg-secondary">Private</span>
                                            <?php endif; ?>
                                        </h5>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="card-text mt-2"><?php echo nl2br(htmlspecialchars($request['request_text'])); ?></p>
                                    
                                    <?php if (is_logged_in()): ?>
                                        <div class="mt-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <button class="btn btn-sm <?php echo $request['has_prayed'] ? 'btn-primary' : 'btn-outline-primary'; ?> pray-button" 
                                                        data-request-id="<?php echo $request['id']; ?>"
                                                        <?php echo $request['has_prayed'] ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-heart<?php echo $request['has_prayed'] ? '-fill' : ''; ?>"></i> 
                                                    <?php echo $request['has_prayed'] ? 'Praying' : "I'm Praying for This"; ?>
                                                </button>
                                                <span class="ms-2 text-muted prayer-count">
                                                    <i class="bi bi-people"></i> 
                                                    <?php echo $request['prayer_count']; ?> 
                                                    <?php echo $request['prayer_count'] == 1 ? 'person' : 'people'; ?> praying
                                                </span>
                                            </div>
                                            
                                            <?php if ($_SESSION['user_id'] == $request['user_id'] || check_permission('church_leader')): ?>
                                                <button class="btn btn-sm btn-outline-danger delete-prayer" 
                                                        data-request-id="<?php echo $request['id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No prayer requests have been submitted yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Handle pray button clicks
        document.querySelectorAll('.pray-button').forEach(button => {
            button.addEventListener('click', function() {
                const requestId = this.dataset.requestId;
                const countElement = this.parentElement.querySelector('.prayer-count');
                const button = this;
                
                fetch('api/pray.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ prayer_id: requestId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.innerHTML = '<i class="bi bi-heart-fill"></i> Praying';
                        button.classList.remove('btn-outline-primary');
                        button.classList.add('btn-primary');
                        button.disabled = true;
                        
                        // Update prayer count
                        countElement.innerHTML = `<i class="bi bi-people"></i> ${data.count} ${data.count == 1 ? 'person' : 'people'} praying`;
                    } else {
                        alert(data.message || 'Failed to record prayer. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to record prayer. Please try again.');
                });
            });
        });

        // Handle delete button clicks
        document.querySelectorAll('.delete-prayer').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this prayer request?')) {
                    const requestId = this.dataset.requestId;
                    const prayerCard = this.closest('.prayer-request');
                    
                    fetch('api/delete_prayer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ prayer_id: requestId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            prayerCard.remove();
                        } else {
                            alert(data.message || 'Failed to delete prayer request. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete prayer request. Please try again.');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
