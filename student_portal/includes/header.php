<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['student'])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['student']['role'] ?? 'student';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Logo and Brand -->
        <div class="d-flex align-items-center">
            <span class="navbar-brand-icon me-2">🎓</span>
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <span class="d-none d-sm-inline">STUDENT</span> PORTAL
            </a>
        </div>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto align-items-center">
                <!-- Navigation Links -->
                <a class="nav-link px-3 d-flex align-items-center" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span>Dashboard</span>
                </a>
                
                <a class="nav-link px-3 d-flex align-items-center" href="courses.php">
                    <i class="fas fa-book me-2"></i>
                    <span>Courses</span>
                </a>
                
                <a class="nav-link px-3 d-flex align-items-center" href="student.php">
                    <i class="fas fa-users me-2"></i>
                    <span>Students</span>
                </a>
                
                <a class="nav-link px-3 d-flex align-items-center" href="profile.php">
                    <i class="fas fa-user-circle me-2"></i>
                    <span>Profile</span>
                </a>
                
                <!-- Logout Button -->
                <a class="nav-link px-3 d-flex align-items-center text-danger" href="logout.php">
                    <i class="fas fa-power-off me-2"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Custom Navbar Styles */
    .navbar {
        padding: 0.8rem 0;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
        border-bottom: 3px solid rgba(255, 255, 255, 0.1);
    }
    
    .navbar-brand {
        font-size: 1.3rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    
    .navbar-brand:hover {
        transform: translateY(-1px);
        text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);
    }
    
    .navbar-brand-icon {
        font-size: 1.5rem;
        animation: subtle-bounce 3s ease-in-out infinite;
    }
    
    @keyframes subtle-bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
    
    .nav-link {
        font-weight: 500;
        border-radius: 8px;
        margin: 0 2px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }
    
    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: white;
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }
    
    .nav-link:hover::after {
        width: 70%;
    }
    
    .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .nav-link i {
        width: 20px;
        text-align: center;
    }
    
    /* Mobile adjustments */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: rgba(30, 64, 175, 0.98);
            padding: 1rem;
            border-radius: 0 0 10px 10px;
            margin-top: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .nav-link {
            padding: 10px 15px !important;
            margin: 3px 0;
        }
        
        .navbar-brand {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 576px) {
        .navbar-brand span.d-none.d-sm-inline {
            display: inline !important;
        }
        
        .nav-link span {
            font-size: 0.95rem;
        }
    }
    
    /* Role indicator for admin */
    <?php if ($role == 'admin'): ?>
    .navbar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
        animation: admin-glow 3s ease-in-out infinite;
    }
    
    @keyframes admin-glow {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }
    
    .navbar-brand::after {
        content: '👑';
        margin-left: 5px;
        font-size: 0.8em;
        vertical-align: super;
    }
    <?php endif; ?>
</style>

<script>
    // Add active class to current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage || 
                (currentPage === '' && linkPage === 'dashboard.php')) {
                link.classList.add('active');
            }
        });
        
        // Add subtle animation to navbar on scroll
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 50) {
                // Scrolling down
                navbar.style.transform = 'translateY(-100%)';
                navbar.style.transition = 'transform 0.3s ease';
            } else {
                // Scrolling up
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });
    });
</script>