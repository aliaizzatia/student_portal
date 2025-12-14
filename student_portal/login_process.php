<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "conn/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $matric = trim($_POST['matric_id'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($matric) || empty($password)) {
        header("Location: index.php?error=Please fill in all fields");
        exit;
    }
    
    // Additional validation
    if (strlen($matric) < 3) {
        header("Location: index.php?error=Invalid matric ID");
        exit;
    }
    
    if (strlen($password) < 6) {
        header("Location: index.php?error=Password must be at least 6 characters");
        exit;
    }
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM students WHERE matric_id = ?");
    
    if (!$stmt) {
        header("Location: index.php?error=Database error. Please try again later.");
        exit;
    }
    
    $stmt->bind_param("s", $matric);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Check if account is active (optional field)
            if (isset($row['status']) && $row['status'] === 'inactive') {
                header("Location: index.php?error=Your account is inactive. Please contact administrator.");
                exit;
            }
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Store user data in session
            $_SESSION['student'] = [
                'id' => $row['id'],
                'matric' => $row['matric_id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'role' => $row['role'] ?? 'student',
                'login_time' => time()
            ];
            
            // Set a session cookie with security flags
            $cookieParams = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                [
                    'expires' => time() + 86400, // 1 day
                    'path' => $cookieParams['path'],
                    'domain' => $cookieParams['domain'],
                    'secure' => isset($_SERVER['HTTPS']), // Use secure cookies if HTTPS
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
            
            // Log login activity (optional)
            logLoginActivity($conn, $row['id']);
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        }
    }
    
    // Invalid credentials
    header("Location: index.php?error=Invalid matric ID or password");
    exit;
} else {
    // Not a POST request
    header("Location: index.php");
    exit;
}

/**
 * Log login activity (optional function)
 */
function logLoginActivity($conn, $student_id) {
    try {
        $stmt = $conn->prepare("INSERT INTO login_logs (student_id, ip_address, user_agent, login_time) VALUES (?, ?, ?, NOW())");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt->bind_param("iss", $student_id, $ip, $agent);
        $stmt->execute();
    } catch (Exception $e) {
        // Silently fail - logging is not critical
        error_log("Failed to log login activity: " . $e->getMessage());
    }
}
?>