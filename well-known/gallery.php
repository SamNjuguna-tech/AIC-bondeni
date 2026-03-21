<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Get all images
$sql = "SELECT * FROM gallery ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - AIC Bondeni</title>
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

    <!-- Hero Section -->
    <div class="hero-section-sm text-center py-0 mb-2" style="background-image: url('assets/images/hero-bg.jpg');">
        <div class="container">
            <h1 class="display-4 text-primary mb-0">Our Gallery</h1>
        </div>
    </div>

    <!-- Gallery Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php while($image = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card h-100 gallery-card">
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($image['title']); ?>"
                                 data-bs-toggle="modal"
                                 data-bs-target="#imageModal"
                                 data-title="<?php echo htmlspecialchars($image['title']); ?>"
                                 data-description="<?php echo htmlspecialchars($image['description']); ?>"
                                 data-image="<?php echo htmlspecialchars($image['image_path']); ?>"
                                 style="height: 250px; object-fit: cover; cursor: pointer;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h5>
                                <p class="card-text small text-muted">
                                    <?php echo htmlspecialchars($image['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Handle image modal
    document.addEventListener('DOMContentLoaded', function() {
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const title = button.getAttribute('data-title');
                const description = button.getAttribute('data-description');
                const image = button.getAttribute('data-image');

                const modal = this;
                modal.querySelector('.modal-title').textContent = title;
                modal.querySelector('.modal-body img').src = image;
                modal.querySelector('.modal-body .description').textContent = description;
            });
        }
    });
    </script>
</body>
</html>
