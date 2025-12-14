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

$student_id = $_SESSION['student']['id'];
$role = $_SESSION['student']['role'];

// Define maximum courses allowed
$MAX_COURSES = 7;

// Handle course registration
if (isset($_GET['register'])) {
    $course_id = intval($_GET['register']);
    
    // Check current enrollment count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM enrollments WHERE student_id = ?");
    $count_stmt->bind_param("i", $student_id);
    $count_stmt->execute();
    $current_count = $count_stmt->get_result()->fetch_assoc()['count'];
    
    // Check if already at maximum
    if ($current_count >= $MAX_COURSES) {
        header("Location: courses.php?error=You have reached the maximum of {$MAX_COURSES} courses");
        exit;
    }
    
    // Check if already registered
    $check = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    $check->bind_param("ii", $student_id, $course_id);
    $check->execute();
    
    if ($check->get_result()->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        header("Location: courses.php?success=Course registered successfully");
    } else {
        header("Location: courses.php?error=You are already registered for this course");
    }
    exit;
}

// Handle course deletion (unregister)
if (isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    
    header("Location: courses.php?success=Course unregistered successfully");
    exit;
}

// Handle class change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class'])) {
    $new_class = trim($_POST['class']);
    
    $stmt = $conn->prepare("UPDATE students SET class = ? WHERE id = ?");
    $stmt->bind_param("si", $new_class, $student_id);
    $stmt->execute();
    
    header("Location: courses.php?success=Class updated successfully");
    exit;
}

// Fetch all courses
$res = $conn->query("SELECT * FROM courses ORDER BY code");
$courses = [];
while ($row = $res->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch student's enrolled courses
$enrolled_stmt = $conn->prepare("SELECT course_id FROM enrollments WHERE student_id = ?");
$enrolled_stmt->bind_param("i", $student_id);
$enrolled_stmt->execute();
$enrolled_result = $enrolled_stmt->get_result();
$enrolled_courses = [];
while ($row = $enrolled_result->fetch_assoc()) {
    $enrolled_courses[] = $row['course_id'];
}

// Fetch student's current class
$stmt = $conn->prepare("SELECT class FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$class_result = $stmt->get_result();
$class = $class_result->fetch_assoc()['class'] ?? 'Not set';

// Count enrolled courses
$enrolled_count = count($enrolled_courses);
$total_courses = count($courses);
$remaining_slots = $MAX_COURSES - $enrolled_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .course-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 14px;
            margin-bottom: 2rem;
        }
        .course-table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }
        .course-card {
            transition: transform 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-3px);
        }
        .max-limit-badge {
            background-color: #ef4444;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        .register-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .course-limit-alert {
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="course-header">
            <h2>📚 Course Registration</h2>
            <p class="mb-0">Browse and register for courses</p>
        </div>
        
        <!-- Course Limit Alert -->
        <?php if ($enrolled_count >= $MAX_COURSES): ?>
            <div class="alert alert-warning course-limit-alert">
                <h6 class="alert-heading">⚠️ Maximum Courses Reached</h6>
                <p class="mb-0">You have registered for <strong><?php echo $enrolled_count; ?> out of <?php echo $MAX_COURSES; ?></strong> courses. You must unregister from a course before registering for a new one.</p>
            </div>
        <?php elseif ($enrolled_count > 0): ?>
            <div class="alert alert-info course-limit-alert">
                <h6 class="alert-heading">ℹ️ Course Registration Status</h6>
                <p class="mb-0">You have registered for <strong><?php echo $enrolled_count; ?> out of <?php echo $MAX_COURSES; ?></strong> courses. <strong><?php echo $remaining_slots; ?> slots remaining</strong>.</p>
            </div>
        <?php endif; ?>
        
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
        
        <!-- Courses Table -->
        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Available Courses</h5>
                <div>
                    <span class="badge bg-primary">Maximum: <?php echo $MAX_COURSES; ?> courses</span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover course-table">
                    <thead class="table-light">
                        <tr>
                            <th width="20%">Course Code</th>
                            <th width="50%">Course Name</th>
                            <th width="15%">Status</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No courses available at the moment.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): 
                                $is_enrolled = in_array($course['id'], $enrolled_courses);
                                $can_register = (!$is_enrolled && $remaining_slots > 0);
                            ?>
                            <tr class="course-card">
                                <td>
                                    <strong class="text-primary"><?php echo htmlspecialchars($course['code']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($course['name']); ?>
                                    <?php if (!empty($course['description'])): ?>
                                        <small class="text-muted d-block mt-1">
                                            <?php echo htmlspecialchars($course['description']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $is_enrolled ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $is_enrolled ? 'Enrolled' : 'Available'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($is_enrolled): ?>
                                        <a href="?delete=<?php echo $course['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to unregister from <?php echo htmlspecialchars($course['code']); ?>?')"
                                           title="Unregister from this course">
                                            Unregister
                                        </a>
                                    <?php else: ?>
                                        <?php if ($can_register): ?>
                                            <a href="?register=<?php echo $course['id']; ?>" 
                                               class="btn btn-sm btn-success" 
                                               title="Register for this course">
                                                Register
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary disabled" 
                                                    title="<?php echo $remaining_slots <= 0 ? 'Maximum courses reached' : 'Already registered'; ?>">
                                                Register
                                            </button>
                                            <?php if ($remaining_slots <= 0): ?>
                                                <small class="max-limit-badge">Max Reached</small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Class Information -->
        <div class="card p-4">
            <h5 class="mb-3">🎓 Class Information</h5>
            <form method="POST">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Current Class:</label>
                            <div class="alert alert-info d-inline-block">
                                <strong><?php echo htmlspecialchars($class); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Change Class:</label>
                            <div class="input-group">
                                <select name="class" class="form-select" required>
                                    <option value="">Select New Class</option>
                                    <option value="Class A" <?php if ($class == 'Class A') echo 'selected'; ?>>Class A</option>
                                    <option value="Class B" <?php if ($class == 'Class B') echo 'selected'; ?>>Class B</option>
                                    <option value="Class C" <?php if ($class == 'Class C') echo 'selected'; ?>>Class C</option>
                                    <option value="Class D" <?php if ($class == 'Class D') echo 'selected'; ?>>Class D</option>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    Update Class
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <?php include "includes/footer.php"; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
        
        // Prevent clicking disabled register buttons
        document.querySelectorAll('.register-btn.disabled').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
        });
    </script>
</body>
</html>