<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "conn/db.php";

// Check if user is logged in
if (!isset($_SESSION['student'])) {
    echo "unauthorized";
    exit;
}

$current_user_id = $_SESSION['student']['id'];
$current_user_role = $_SESSION['student']['role'];

if (isset($_POST['update_student'])) {
    $id = intval($_POST['student_id']);
    
    // Security check: Users can only edit their own info unless they're admin
    if ($current_user_role != 'admin' && $id != $current_user_id) {
        echo "unauthorized";
        exit;
    }
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email)) {
        echo "error";
        exit;
    }
    
    // Check if email already exists for another student
    $check = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "email_exists";
        exit;
    }
    
    // Update student
    $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $phone, $id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "invalid_request";
}
?>