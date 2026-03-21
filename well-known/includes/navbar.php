<?php
if (!isset($_SESSION)) {
    session_start();
}

// Determine if we're in admin directory
$in_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base_path = $in_admin ? '../' : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">AIC Bondeni Church</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
           <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sermons.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>sermons.php">Sermons</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>events.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ministries.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>ministries.php">Ministries</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'prayer.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>prayer.php">Prayer Requests</a>
                </li>
                <li class="nav-item">
                    <?php if (!is_logged_in()): ?>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'join-family.php' ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>join-family.php">Join the Family</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'donate.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base_path; ?>donate.php">Give</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ((check_permission('member') || check_permission('church_leader')) && !check_permission('admin')): ?>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>account.php">Profile</a></li>
                            <?php endif; ?>

                            <?php if (check_permission('church_leader') && !check_permission('admin')): ?>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/index.php">Dashboard</a></li>
                            <?php endif; ?>

                            <?php if (check_permission('admin')): ?>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Admin Controls</h6></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/">
                                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/manage_sermons.php">
                                    <i class="bi bi-book"></i> Sermons
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/manage_events.php">
                                    <i class="bi bi-calendar-event"></i> Events
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/gallery.php">
                                    <i class="bi bi-images"></i> Gallery
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/manage_prayers.php">
                                    <i class="fas fa-pray"></i> Prayers
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/manage_users.php">
                                    <i class="bi bi-people"></i> Users
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/settings.php">
                                    <i class="bi bi-gear"></i> Settings
                                </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base_path; ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>