<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "conn/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $matric = trim($_POST['matric_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    // Check for empty fields
    if (empty($matric) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "All fields are required";
    }
    
    // Validate email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate matric ID (alphanumeric, 6-20 characters)
    if (!empty($matric) && !preg_match('/^[a-zA-Z0-9]{6,20}$/', $matric)) {
        $errors[] = "Matric ID must be 6-20 alphanumeric characters";
    }
    
    // Check password match
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match";
    }
    
    // Check password length
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // Check password strength (optional)
    if (strlen($password) > 0 && strlen($password) < 6) {
        $errors[] = "Password is too weak. Use at least 6 characters";
    }
    
    // If no errors so far, check database for duplicates
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM students WHERE matric_id = ? OR email = ?");
        $stmt->bind_param("ss", $matric, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Check which one exists
            $stmt2 = $conn->prepare("SELECT id FROM students WHERE matric_id = ?");
            $stmt2->bind_param("s", $matric);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            if ($result2->num_rows > 0) {
                $errors[] = "Matric ID already exists";
            } else {
                $errors[] = "Email already exists";
            }
        }
    }
    
    // If there are errors, redirect back with errors
    if (!empty($errors)) {
        $error_string = implode("; ", $errors);
        header("Location: register.php?error=" . urlencode($error_string));
        exit;
    }
    
    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'student'; // default role
    
    // Generate a random name based on matric ID for display purposes
    $name = "Student_" . substr($matric, 0, 4);
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO students (matric_id, email, password, name, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $matric, $email, $hash, $name, $role);
    
    if ($stmt->execute()) {
        // Get the inserted student ID
        $student_id = $stmt->insert_id;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Auto-login after registration
        $_SESSION['student'] = [
            'id' => $student_id,
            'matric' => $matric,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'login_time' => time()
        ];
        
        // Set secure session cookie
        $cookieParams = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            [
                'expires' => time() + 86400,
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
        
        // Log registration activity (optional)
        logRegistrationActivity($conn, $student_id, $matric);
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        // Database error
        error_log("Registration failed: " . $stmt->error);
        header("Location: register.php?error=Registration failed. Please try again or contact support.");
        exit;
    }
} else {
    // Not a POST request
    header("Location: register.php");
    exit;
}

/**
 * Log registration activity (optional function)
 */
function logRegistrationActivity($conn, $student_id, $matric) {
    try {
        $stmt = $conn->prepare("INSERT INTO registration_logs (student_id, matric_id, ip_address, user_agent, registered_at) VALUES (?, ?, ?, ?, NOW())");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt->bind_param("isss", $student_id, $matric, $ip, $agent);
        $stmt->execute();
    } catch (Exception $e) {
        // Silently fail - logging is not critical
        error_log("Failed to log registration activity: " . $e->getMessage());
    }
}
?>