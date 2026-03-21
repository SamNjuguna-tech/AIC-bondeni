<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Give - Our Church</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
  <link rel="manifest" href="site.webmanifest">
</head>
<body class="bg-light">

  <?php include 'includes/navbar.php'; ?>

  <div class="bg-primary bg-gradient text-white py-5">
    <div class="container py-4 position-relative">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
          <h1 class="display-4 fw-bold mb-3">Support Our Ministry</h1>
          <p class="lead mb-4">Your generous giving enables us to spread God's love and serve our community effectively</p>
          <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="#donate-now" class="btn btn-light btn-lg px-4 rounded-pill fw-medium">Give Now</a>
            <a href="#why-give" class="btn btn-outline-light btn-lg px-4 rounded-pill fw-medium">Why Give?</a>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- Giving detauils -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold mb-3">Ways to Support Our Mission</h2>
        <p class="lead text-muted">Your generosity fuels our ministry and transforms lives in our community</p>
        <div class="mx-auto bg-primary" style="height: 3px; width: 80px;"></div>
      </div>

      <div class="card border-0 shadow mb-5" id="donate-now">
        <div class="card-body p-4 p-lg-5">
          <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
              <h3 class="fw-bold mb-3">Make a Contribution</h3>
              <p class="text-muted mb-4">Choose the most convenient method for your donation:</p>

              <div class="d-flex align-items-start mb-4">
                <div class="me-3 text-primary">
                  <i class="bi bi-phone fs-2"></i>
                </div>
                <div>
                  <h5 class="mb-1">Mobile Giving (M-Pesa)</h5>
                  <p class="mb-1"><strong>Paybill Number:</strong> 247247</p>
                  <p class="mb-0"><strong>Account Number:</strong> 710712#purpose</p>
                  <small class="text-muted">(Include purpose in reference if desired)</small>
                </div>
              </div>

              <div class="d-flex align-items-start mb-4">
                <div class="me-3 text-primary">
                  <i class="bi bi-cash-coin fs-2"></i>
                </div>
                <div>
                  <h5 class="mb-1">Sunday Offering</h5>
                  <p class="mb-0">Place your gift in the offering basket during worship services</p>
                </div>
              </div>

              <div class="d-flex align-items-start">
                <div class="me-3 text-primary">
                  <i class="bi bi-building fs-2"></i>
                </div>
                <div>
                  <h5 class="mb-1">Church Office</h5>
                  <p class="mb-0">Visit us Monday-Friday, 9am-4pm to make your donation</p>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="bg-light p-4 rounded-3 h-100">
                <h5 class="mb-3">Our Commitment to Stewardship</h5>
                <p class="mb-4">We honor your trust through responsible financial management:</p>
                <div class="d-flex align-items-start mb-3">
                  <i class="bi bi-check-circle-fill text-success mt-1 me-2"></i>
                  <span>Detailed financial statements reviewed by our leadership team</span>
                </div>
                <div class="d-flex align-items-start mb-3">
                  <i class="bi bi-check-circle-fill text-success mt-1 me-2"></i>
                  <span>Professional annual audits ensure accountability</span>
                </div>
                <div class="d-flex align-items-start">
                  <i class="bi bi-check-circle-fill text-success mt-1 me-2"></i>
                  <span>Restricted gifts used exclusively for their designated purposes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center mb-5" id="why-give">
        <h2 class="display-5 fw-bold mb-3">The Impact of Your Giving</h2>
        <p class="lead text-muted">See how your support makes a tangible difference</p>
        <div class="mx-auto bg-primary" style="height: 3px; width: 80px;"></div>
      </div>

      <div class="row g-4">
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
              <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px;">
                <i class="bi bi-people-fill fs-3"></i>
              </div>
              <h5 class="mb-3">Community Care</h5>
              <p class="text-muted mb-0">Feeding programs, counseling services, and support for families in need</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
              <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px;">
                <i class="bi bi-heart-fill fs-3"></i>
              </div>
              <h5 class="mb-3">Children & Youth</h5>
              <p class="text-muted mb-0">Sunday School, youth camps, and discipleship programs for young believers</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
              <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px;">
                <i class="bi bi-globe2 fs-3"></i>
              </div>
              <h5 class="mb-3">Global Missions</h5>
              <p class="text-muted mb-0">Supporting evangelism and church planting efforts worldwide</p>
            </div>
          </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center p-4">
              <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3 mx-auto" style="width: 60px; height: 60px;">
                <i class="bi bi-house-fill fs-3"></i>
              </div>
              <h5 class="mb-3">Ministry Support</h5>
              <p class="text-muted mb-0">Maintaining our facilities and supporting pastoral staff and ministries</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  <?php include 'includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // scroll -> give now || why give buttons
    ['why-give', 'donate-now'].forEach(id => {
      document.querySelector(`a[href="#${id}"]`).addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
  </script>
</body>
</html>
