<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AIC Bondeni Church</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <style>
        .about-section {
            padding: 80px 0;
        }
        .map-container {
            margin: 40px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .service-times {
            background-color: var(--dark-blue);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .service-times h3 {
            color: white;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section" style="height: 30vh;">
        <div class="hero-content">
            <h1>About Us</h1>
            <p class="lead">Welcome to AIC Bondeni Church</p>
        </div>
    </div>

    <!-- About Section -->
    <div class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2 class="mb-4">Our Story</h2>
                    <p>AIC Bondeni Church has been serving the community for many years, providing a place of worship, fellowship, and spiritual growth. We are committed to spreading the gospel and making a positive impact in our community.</p>
                    
                    <h3 class="mt-4 mb-3">Our Vision</h3>
                    <p>A Christ-centrered community transforming lives through the power of the gospel.</p>

                    <h3 class="mt-4 mb-3">Our Mission</h3>
                    <p>To exalt the LORD God, make disciples of Christ and equip them for ministry.</p>
                    
                    <div class="service-times">
                        <h3><i class="bi bi-clock me-2"></i>Service Times</h3>
                        <ul class="list-unstyled">
                            <li><label class="mb-2">Christian Men Fellowship</label>: <label for="r2">7-8am</label><br></li>
                            <li><label class="mb-2">Sunday School</label>: <label for="r2">8-9am</label><br></li>
                            <li><label class="mb-2">First Service</label>: <label for="r2">8:30-10:30am</label><br></li>
                            <li><label class="mb-2">Youth Service</label>: <label for="r2">11-1pm</label><br></li>
                            <li><label class="">Second Service</label>: <label for="r2">11-1pm</label><br></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d8956.718830835936!2d36.07892107349913!3d-0.2968470351668664!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x18298dfbc45640a9%3A0xe08a0910aec692c5!2sAIC%20Bondeni!5e0!3m2!1sen!2sus!4v1743200419345!5m2!1sen!2sus" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
