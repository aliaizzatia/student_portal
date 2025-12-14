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
    <title>Student Portal | Register</title>
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
        
        .register-card {
            width: 420px;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .register-emoji {
            font-size: 72px;
            margin-bottom: 20px;
            display: block;
        }
        
        .register-title {
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
        
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .password-requirements {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid #4a6cf7;
        }
        
        .password-requirements h6 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .password-requirements ul {
            margin-bottom: 0;
            padding-left: 1.25rem;
        }
        
        .password-requirements li {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card register-card border-0">
            <div class="text-center">
                <h4 class="register-title mb-2">CREATE ACCOUNT</h4>
                <p class="text-center text-muted mb-4">Join our student portal community</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        $errors = explode("; ", htmlspecialchars($_GET['error']));
                        foreach ($errors as $error) {
                            echo "<div>{$error}</div>";
                        }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="register_process.php" method="POST" id="registerForm">
                <div class="mb-3">
                    <label for="matric_id" class="form-label">Matric ID <span class="text-danger">*</span></label>
                    <input type="text" name="matric_id" class="form-control" placeholder="Enter your matric ID" required>
                    
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required minlength="6">
                    
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" required>
                    <div id="passwordMatch" class="form-text"></div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 mt-3">CREATE ACCOUNT</button>
            </form>
            
            <p class="text-center mt-4 mb-0">
                Already have an account? <a href="index.php" class="fw-medium">Login here</a>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const passwordMatch = document.getElementById('passwordMatch');
            
            if (password !== confirmPassword) {
                e.preventDefault();
                passwordMatch.innerHTML = '<span class="text-danger">❌ Passwords do not match</span>';
                document.getElementById('confirm_password').focus();
            } else {
                passwordMatch.innerHTML = '<span class="text-success">✅ Passwords match</span>';
            }
        });
        
        // Real-time password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const passwordMatch = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                passwordMatch.innerHTML = 'Please confirm your password';
                passwordMatch.className = 'form-text';
            } else if (password === confirmPassword) {
                passwordMatch.innerHTML = '<span class="text-success">✅ Passwords match</span>';
            } else {
                passwordMatch.innerHTML = '<span class="text-danger">❌ Passwords do not match</span>';
            }
        });
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.createElement('div');
            let strength = 'Weak';
            let color = 'danger';
            
            if (password.length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) {
                strength = 'Very Strong';
                color = 'success';
            } else if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                strength = 'Strong';
                color = 'success';
            } else if (password.length >= 6) {
                strength = 'Medium';
                color = 'warning';
            }
            
            // Update or create strength indicator
            let indicator = document.getElementById('passwordStrength');
            if (!indicator) {
                indicator = document.createElement('div');
                indicator.id = 'passwordStrength';
                indicator.className = 'form-text';
                this.parentNode.appendChild(indicator);
            }
            indicator.innerHTML = `Password strength: <span class="text-${color}">${strength}</span>`;
        });
    </script>
</body>
</html>