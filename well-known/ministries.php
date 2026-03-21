
<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Fetch all active ministries
$stmt = $conn->prepare("SELECT m.*, 
    (SELECT COUNT(*) FROM ministry_members WHERE ministry_id = m.id) as member_count,
    (SELECT COUNT(*) FROM ministry_members WHERE ministry_id = m.id AND user_id = ?) as is_member
    FROM ministries m WHERE m.is_active = 1 ORDER BY m.name");
$user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ministries = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ministries - Our Church</title>
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

    <!-- Toast Container for Notifications -->
    <div id="toast-container"></div>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Our Ministries</h1>
                <p class="lead">Get involved and grow in your faith through our various ministries</p>
            </div>
            <?php if (check_permission('admin')): ?>
            <div class="col-md-4 text-end">
                <a href="admin/manage_ministries.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Manage Ministries
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php
            if ($ministries->num_rows > 0) {
                while ($ministry = $ministries->fetch_assoc()) {
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card ministry-card">
                            <img src="<?php echo !empty($ministry['image_url']) ? htmlspecialchars($ministry['image_url']) : 'assets/images/ministry-placeholder.jpg'; ?>" 
                                 class="card-img-top ministry-image" alt="<?php echo htmlspecialchars($ministry['name']); ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($ministry['name']); ?></h5>
                                    <span class="member-count">
                                        <i class="bi bi-people-fill"></i> <?php echo $ministry['member_count']; ?> members
                                    </span>
                                </div>
                                <?php if (!empty($ministry['leader'])): ?>
                                    <p class="card-text ministry-leader">
                                        <i class="bi bi-person-circle"></i> Led by <?php echo htmlspecialchars($ministry['leader']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($ministry['description'])); ?></p>
                                <?php if (!empty($ministry['meeting_time']) || !empty($ministry['location'])): ?>
                                    <div class="mt-3">
                                        <?php if (!empty($ministry['meeting_time'])): ?>
                                            <p class="mb-1">
                                                <i class="bi bi-clock"></i> <?php echo htmlspecialchars($ministry['meeting_time']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($ministry['location'])): ?>
                                            <p class="mb-1">
                                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ministry['location']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent">
                                <?php if (is_logged_in()): ?>
                                    <?php if ($ministry['is_member']): ?>
                                        <button class="btn btn-leave-ministry w-100 ministry-action" 
                                                data-ministry-id="<?php echo $ministry['id']; ?>"
                                                data-action="leave">
                                            <i class="bi bi-box-arrow-right"></i> Leave Ministry
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary w-100 ministry-action" 
                                                data-ministry-id="<?php echo $ministry['id']; ?>"
                                                data-action="join">
                                            <i class="bi bi-plus-circle"></i> Join Ministry
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-box-arrow-in-right"></i> Login to Join
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No active ministries at the moment. Please check back later.
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        document.querySelectorAll('.ministry-action').forEach(button => {
            button.addEventListener('click', function() {
                const ministryId = this.getAttribute('data-ministry-id');
                const action = this.getAttribute('data-action');
                const button = this;
                
                // Disable button while processing
                button.disabled = true;
                
                fetch('handlers/join_ministry.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `ministry_id=${ministryId}&action=${action}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        
                        // Update button
                        if (action === 'join') {
                            button.innerHTML = '<i class="bi bi-box-arrow-right"></i> Leave Ministry';
                            button.classList.remove('btn-primary');
                            button.classList.add('btn-leave-ministry');
                            button.setAttribute('data-action', 'leave');
                        } else {
                            button.innerHTML = '<i class="bi bi-plus-circle"></i> Join Ministry';
                            button.classList.remove('btn-leave-ministry');
                            button.classList.add('btn-primary');
                            button.setAttribute('data-action', 'join');
                        }
                        
                        // Update member count
                        const memberCountEl = button.closest('.card').querySelector('.member-count');
                        let count = parseInt(memberCountEl.textContent);
                        count = action === 'join' ? count + 1 : count - 1;
                        memberCountEl.innerHTML = `<i class="bi bi-people-fill"></i> ${count} members`;
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'danger');
                    console.error('Error:', error);
                })
                .finally(() => {
                    button.disabled = false;
                });
            });
        });
    </script>
</body>
</html>
