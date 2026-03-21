<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Please enter your email address';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            try {
                // Check if email exists
                $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user) {
                    // Generate password reset token (expires in 1 hour)
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Store token in database
                    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $token, $expires, $user['id']);
                    $stmt->execute();
                    
                    // Send email with reset link
                    $resetLink = "https://".$_SERVER['HTTP_HOST']."/reset-password.php?token=$token";
                    $subject = "Password Reset Request";
                    $message = "Hello ".htmlspecialchars($user['username']).",\n\n";
                    $message .= "You requested a password reset. Click the link below to reset your password:\n\n";
                    $message .= $resetLink."\n\n";
                    $message .= "This link will expire in 1 hour.\n";
                    $message .= "If you didn't request this, please ignore this email.\n";
                    
                    // In production, use a proper mailer library
                    $headers = "From: no-reply@".$_SERVER['HTTP_HOST']."\r\n";
                    mail($email, $subject, $message, $headers);
                    
                    $success = 'Password reset link has been sent to your email';
                } else {
                    // Don't reveal whether email exists
                    $success = 'If your email exists in our system, you will receive a password reset link';
                }
            } catch (Exception $e) {
                error_log("Database error: ".$e->getMessage());
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Our Church</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card auth-container">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Forgot Password</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php else: ?>
                            <p>Enter your email address and we'll send you a link to reset your password.</p>
                            
                            <form method="POST" action="forgot-password.php">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <p>Remember your password? <a href="login.php">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>