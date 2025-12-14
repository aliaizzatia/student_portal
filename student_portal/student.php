<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['student'])) {
    header("Location: index.php");
    exit;
}

require_once "conn/db.php";

$current_user_id = $_SESSION['student']['id'];
$current_user_role = $_SESSION['student']['role'];

// Handle update student info
if (isset($_POST['update_student'])) {
    $id = intval($_POST['student_id']);
    
    // Security check: Users can only edit their own info unless they're admin
    if ($current_user_role != 'admin' && $id != $current_user_id) {
        $error = "You can only edit your own information!";
    } else {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        $stmt = $conn->prepare("UPDATE students SET name=?, email=?, phone=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $phone, $id);
        
        if ($stmt->execute()) {
            $success = "Student information updated successfully!";
            
            // Update session if user updated their own info
            if ($id == $current_user_id) {
                $_SESSION['student']['name'] = $name;
            }
        } else {
            $error = "Failed to update student information.";
        }
    }
}

// Fetch all students
$res = $conn->query("SELECT * FROM students ORDER BY name");
$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}
$totalStudents = count($students);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .student-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 14px;
            margin-bottom: 2rem;
        }
        .student-table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }
        .student-card {
            transition: transform 0.3s ease;
        }
        .student-card:hover {
            transform: translateY(-3px);
        }
        .role-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
        .current-user-row {
            background-color: rgba(59, 130, 246, 0.05) !important;
            border-left: 3px solid #3b82f6;
        }
        .view-only {
            color: #6c757d;
            font-style: italic;
        }
        .edit-info {
            color: #10b981;
            font-weight: 500;
        }
        .you-badge {
            background-color: #3b82f6;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="student-header">
            <h2>👨🏻‍🎓👩🏻‍🎓 Student Directory</h2>
            <p class="mb-0">View student information</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Information Box -->
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading">ℹ️ Edit Permissions</h6>
            <ul class="mb-0">
                <li><strong>Students:</strong> Can edit <span class="edit-info">only their own information</span></li>
                <li><strong>Administrators:</strong> Can edit <span class="text-primary">all student information</span></li>
                <li>Your row is highlighted in <span class="text-primary">blue</span> with a "You" badge</li>
            </ul>
        </div>
        
        <!-- Students Table -->
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">📋 Registered Students List</h5>
                <span class="badge bg-primary">Total: <?php echo $totalStudents; ?></span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover student-table">
                    <thead class="table-light">
                        <tr>
                            <th width="15%">Matric ID</th>
                            <th width="25%">Full Name</th>
                            <th width="25%">Email</th>
                            <th width="15%">Phone</th>
                            <th width="10%">Role</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No students registered yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): 
                                $is_current_user = ($student['id'] == $current_user_id);
                                $can_edit = ($current_user_role == 'admin' || $is_current_user);
                            ?>
                            <tr class="student-card <?php echo $is_current_user ? 'current-user-row' : ''; ?>">
                                <td>
                                    <strong class="text-primary"><?php echo htmlspecialchars($student['matric_id']); ?></strong>
                                    <?php if ($is_current_user): ?>
                                        <span class="you-badge">You</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           value="<?php echo htmlspecialchars($student['name']); ?>" 
                                           <?php if (!$can_edit) echo 'disabled'; ?> 
                                           id="name-<?php echo $student['id']; ?>">
                                </td>
                                <td>
                                    <input type="email" 
                                           class="form-control form-control-sm" 
                                           value="<?php echo htmlspecialchars($student['email']); ?>" 
                                           <?php if (!$can_edit) echo 'disabled'; ?> 
                                           id="email-<?php echo $student['id']; ?>">
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" 
                                           <?php if (!$can_edit) echo 'disabled'; ?> 
                                           id="phone-<?php echo $student['id']; ?>">
                                </td>
                                <td>
                                    <span class="badge <?php echo $student['role'] == 'admin' ? 'bg-danger' : 'bg-success'; ?> role-badge">
                                        <?php echo ucfirst($student['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($can_edit): ?>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" 
                                                    class="btn btn-outline-warning"
                                                    onclick="enableEdit(<?php echo $student['id']; ?>)"
                                                    id="edit-btn-<?php echo $student['id']; ?>">
                                                ✏️ Edit
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-success"
                                                    onclick="saveEdit(<?php echo $student['id']; ?>)"
                                                    id="save-btn-<?php echo $student['id']; ?>"
                                                    disabled>
                                                💾 Save
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="view-only">View Only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <small class="text-muted">
                    <span class="text-primary">📝 Note:</span> 
                    <?php if ($current_user_role == 'admin'): ?>
                        You have administrator privileges to edit all student information.
                    <?php else: ?>
                        You can edit only your own information (highlighted row).
                    <?php endif; ?>
                </small>
            </div>
        </div>
        
        <?php include "includes/footer.php"; ?>
    </div>

    <script>
    function enableEdit(studentId) {
        // Enable inputs for this student
        document.getElementById('name-' + studentId).disabled = false;
        document.getElementById('email-' + studentId).disabled = false;
        document.getElementById('phone-' + studentId).disabled = false;
        
        // Toggle buttons
        document.getElementById('edit-btn-' + studentId).disabled = true;
        document.getElementById('save-btn-' + studentId).disabled = false;
        
        // Focus on name field
        document.getElementById('name-' + studentId).focus();
    }
    
    function saveEdit(studentId) {
        const name = document.getElementById('name-' + studentId).value;
        const email = document.getElementById('email-' + studentId).value;
        const phone = document.getElementById('phone-' + studentId).value;
        
        // Basic validation
        if (!name.trim() || !email.trim()) {
            alert('Name and email are required!');
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('update_student', '1');
        formData.append('student_id', studentId);
        formData.append('name', name);
        formData.append('email', email);
        formData.append('phone', phone);
        
        // Show loading state
        const saveBtn = document.getElementById('save-btn-' + studentId);
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '⏳ Saving...';
        saveBtn.disabled = true;
        
        // Send update request
        fetch('student_update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                // Disable inputs
                document.getElementById('name-' + studentId).disabled = true;
                document.getElementById('email-' + studentId).disabled = true;
                document.getElementById('phone-' + studentId).disabled = true;
                
                // Toggle buttons
                document.getElementById('edit-btn-' + studentId).disabled = false;
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = true;
                
                // Show success message
                alert('Information updated successfully!');
                
                // If editing own profile, reload to update session
                const youBadge = document.querySelector(`#save-btn-${studentId}`).closest('tr').querySelector('.you-badge');
                if (youBadge) {
                    window.location.reload();
                }
            } else if (data.trim() === 'email_exists') {
                alert('This email is already registered by another student!');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            } else if (data.trim() === 'unauthorized') {
                alert('You are not authorized to edit this student!');
                window.location.reload();
            } else {
                alert('Error updating information. Please try again.');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating information. Please try again.');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>