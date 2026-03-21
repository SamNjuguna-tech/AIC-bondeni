<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark" id="sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <?php
                $username = $_SESSION['username'] ?? $user['username'] ?? $admin['username'] ?? 'Guest';
                $role = $_SESSION['role'] ?? $admin['role'] ?? 'Unknown';
                $safe_username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                $safe_role = htmlspecialchars($role, ENT_QUOTES, 'UTF-8');
            ?>

            <h5 class="text-white mb-1 text-capitalize"><?php  echo $safe_username;   ?></h5>
            <span class="badge bg-primary text-capitalize"><?php  echo $safe_role;   ?></span>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>" href="manage_users.php">
                    <i class="fas fa-users me-2"></i> Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_ministries.php' ? 'active' : ''; ?>" href="manage_ministries.php">
                    <i class="fas fa-hands-praying me-2"></i> Ministries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_prayers.php' ? 'active' : ''; ?>" href="manage_prayers.php">
                    <i class="fas fa-pray me-2"></i> Prayer Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_sermons.php' ? 'active' : ''; ?>" href="manage_sermons.php">
                    <i class="fas fa-bible me-2"></i> Sermons
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_events.php' ? 'active' : ''; ?>" href="manage_events.php">
                    <i class="fas fa-calendar-alt me-2"></i> Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>" href="gallery.php">
                    <i class="fas fa-images me-2"></i> Gallery
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_join_requests.php' ? 'active' : ''; ?>" href="manage_join_requests.php">
                    <i class="fas fa-user-plus me-2"></i></i> Family Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-gear me-2"></i> Settings
                </a>
            </li>
            <li class="nav-item mt-3 bg-danger">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-door-open me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
