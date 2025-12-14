<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['student'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            min-height: 100vh;
            background-image: url('image/img2.jpg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            background-attachment: fixed;
        }
        
        .login-card {
            width: 400px;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .login-emoji {
            font-size: 72px;
            margin-bottom: 20px;
            display: block;
        }
        
        .login-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
        }
        
        .form-control:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .container {
            position: relative;
            z-index: 1;
        }
        
        /* Overlay for better readability */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 0;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        a {
            color: #4a6cf7;
            text-decoration: none;
            font-weight: 500;
        }
        
        a:hover {
            color: #3a5ce5;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card login-card border-0">
            <div class="text-center">
                <span class="login-emoji">🎓</span>
                <h4 class="login-title mb-2">STUDENT PORTAL</h4>
                <p class="text-center text-muted mb-4">Welcome back! Please login to continue.</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="login_process.php" method="POST">
                <div class="mb-3">
                    <label for="matric_id" class="form-label">Matric ID</label>
                    <input type="text" name="matric_id" class="form-control" placeholder="Enter your matric ID" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">LOGIN</button>
            </form>
            
            <p class="text-center mt-4 mb-0">
                Don't have an account? <a href="register.php" class="fw-medium">Register here</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>