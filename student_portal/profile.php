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

$id = $_SESSION['student']['id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $program = $_POST['program'] ?? '';
    $semester = $_POST['semester'] ?? '';

    // Profile picture upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_name = time() . '_' . basename($_FILES['photo']['name']);
        $target_dir = "uploads/";
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $target_file = $target_dir . $photo_name;
        
        // Check file type
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, program=?, semester=?, photo=? WHERE id=?");
                $stmt->bind_param("ssssssi", $name, $email, $phone, $program, $semester, $photo_name, $id);
            }
        }
    } else {
        $stmt = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, program=?, semester=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $program, $semester, $id);
    }
    
    $stmt->execute();
    header("Location: profile.php?success=Profile updated successfully");
    exit;
}

// Fetch student info
$res = $conn->prepare("SELECT * FROM students WHERE id=?");
$res->bind_param("i", $id);
$res->execute();
$student = $res->get_result()->fetch_assoc();

// Fetch enrolled courses
$courses = [];
$res2 = $conn->prepare("SELECT c.code, c.name FROM courses c INNER JOIN enrollments e ON c.id=e.course_id WHERE e.student_id=?");
$res2->bind_param("i", $id);
$res2->execute();
$result2 = $res2->get_result();
while ($row = $result2->fetch_assoc()) {
    $courses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Modern Card Design */
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Profile Card Design */
        .profile-card {
            background: white;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            height: auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 0;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        .avatar-container {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0 auto 1.5rem;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .student-name {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .student-matric {
            color: #718096;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .status-badge {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 2rem;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }
        
        /* Info Items */
        .info-section {
            margin-top: 2rem;
        }
        
        .info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-icon {
            width: 24px;
            color: #667eea;
            font-size: 1.1rem;
            text-align: center;
            margin-right: 12px;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 500;
            margin-bottom: 2px;
        }
        
        .info-value {
            font-size: 1rem;
            color: #2d3748;
            font-weight: 500;
        }
        
        /* Courses Section */
        .courses-section {
            background: white;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .courses-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .courses-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .courses-count {
            background: #667eea;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 600;
        }
        
        /* Courses List */
        .courses-list-container {
            margin-top: 1rem;
        }
        
        .course-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .course-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e5e7eb;
            gap: 12px;
        }
        
        .course-item:last-child {
            border-bottom: none;
        }
        
        .course-icon-small {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .course-details {
            flex: 1;
            min-width: 0;
        }
        
        .course-code-bold {
            font-size: 0.95rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 2px;
        }
        
        .course-name {
            font-size: 0.85rem;
            color: #6b7280;
            line-height: 1.4;
            word-break: break-word;
        }
        
        .no-courses {
            text-align: center;
            padding: 2rem 1rem;
            color: #9ca3af;
            font-style: italic;
        }
        
        /* Edit Form Design - FULL WIDTH AT BOTTOM */
        .form-section {
            margin-top: 2.5rem;
        }
        
        .form-card {
            background: white;
            border-radius: 18px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .form-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control, .form-select {
            padding: 0.85rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
            outline: none;
        }
        
        .image-upload {
            text-align: center;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 2rem;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .image-upload:hover {
            border-color: #667eea;
            background: #f8fafc;
        }
        
        .image-upload input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            display: block;
        }
        
        .upload-text {
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .upload-hint {
            color: #9ca3af;
            font-size: 0.85rem;
        }
        
        .image-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 1rem;
            display: none;
        }
        
        .update-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }
        
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        
        .update-btn:active {
            transform: translateY(0);
        }
        
        /* Alerts */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .profile-container {
                padding: 0 15px;
            }
            
            .form-card {
                padding: 2rem 1.5rem;
            }
            
            .courses-section {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 50px;
            }
            
            .student-name {
                font-size: 1.4rem;
            }
            
            .profile-card, .form-card, .courses-section {
                padding: 1.5rem;
            }
            
            .row {
                flex-direction: column;
            }
            
            .col-lg-6 {
                width: 100%;
            }
            
            .form-section {
                margin-top: 1.5rem;
            }
            
            .courses-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
        
        @media (max-width: 576px) {
            .course-item {
                flex-direction: column;
                text-align: center;
                padding: 1rem 0;
            }
            
            .course-icon-small {
                margin-bottom: 0.5rem;
            }
            
            .form-card {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="profile-container mt-4">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- UPPER SECTION: Profile Info + Enrolled Courses (Side by Side) -->
        <div class="row g-4">
            <!-- LEFT COLUMN: Profile Info -->
            <div class="col-lg-6">
                <div class="profile-card">
                    <div class="avatar-container">
                        <div class="profile-avatar">
                            <?php if (!empty($student['photo'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($student['name']); ?>">
                            <?php else: ?>
                                <i class="fas fa-user-graduate"></i>
                            <?php endif; ?>
                        </div>
                        <h2 class="student-name"><?php echo htmlspecialchars($student['name']); ?></h2>
                        <p class="student-matric">Matric ID: <?php echo htmlspecialchars($student['matric_id']); ?></p>
                        <span class="status-badge">✅ Active Student</span>
                    </div>
                    
                    <!-- Personal Information -->
                    <div class="info-section">
                        <h3 class="info-title">
                            <i class="fas fa-info-circle"></i>
                            Personal Information
                        </h3>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['phone'] ?? 'Not provided'); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Program</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['program'] ?? 'Not specified'); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Semester</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['semester'] ?? '1'); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Class</div>
                                <div class="info-value"><?php echo htmlspecialchars($student['class'] ?? 'Not assigned'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- RIGHT COLUMN: Enrolled Courses -->
            <div class="col-lg-6">
                <div class="courses-section">
                    <div class="courses-header">
                        <div class="courses-title">📚 ENROLLED COURSES</div>
                        <div class="courses-count"><?php echo count($courses); ?></div>
                    </div>
                    
                    <?php if (empty($courses)): ?>
                        <div class="no-courses">
                            <i class="fas fa-book-open mb-3" style="font-size: 2rem; color: #cbd5e1;"></i>
                            <p>No courses enrolled yet</p>
                        </div>
                    <?php else: ?>
                        <div class="courses-list-container">
                            <div class="course-box">
                                <?php foreach ($courses as $course): 
                                    $initial = substr($course['code'], 0, 1);
                                ?>
                                <div class="course-item">
                                    <div class="course-icon-small"><?php echo $initial; ?></div>
                                    <div class="course-details">
                                        <div class="course-code-bold"><?php echo htmlspecialchars($course['code']); ?></div>
                                        <div class="course-name"><?php echo htmlspecialchars($course['name']); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- LOWER SECTION: Edit Profile Form (Full Width) -->
        <div class="form-section">
            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-edit" style="font-size: 1.8rem; color: #667eea;"></i>
                    <h2 class="form-title">Edit Profile Information</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user"></i>
                                    Full Name
                                </label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['name']); ?>" 
                                       placeholder="Enter your full name" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['email']); ?>" 
                                       placeholder="Enter your email address" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Phone Number
                                </label>
                                <input type="text" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" 
                                       placeholder="Enter phone number">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-graduation-cap"></i>
                                    Program
                                </label>
                                <select name="program" class="form-select">
                                    <option value="">Select Program</option>
                                    <option value="Computer Science" <?php echo ($student['program'] ?? '') == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Information System" <?php echo ($student['program'] ?? '') == 'Information System' ? 'selected' : ''; ?>>Information System</option>
                                    <option value="Software Engineering" <?php echo ($student['program'] ?? '') == 'Software Engineering' ? 'selected' : ''; ?>>Software Engineering</option>
                                    <option value="Data Science" <?php echo ($student['program'] ?? '') == 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                                    <option value="Cyber Security" <?php echo ($student['program'] ?? '') == 'Cyber Security' ? 'selected' : ''; ?>>Cyber Security</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Semester
                                </label>
                                <select name="semester" class="form-select">
                                    <option value="1" <?php echo ($student['semester'] ?? '') == '1' ? 'selected' : ''; ?>>Semester 1</option>
                                    <option value="2" <?php echo ($student['semester'] ?? '') == '2' ? 'selected' : ''; ?>>Semester 2</option>
                                    <option value="3" <?php echo ($student['semester'] ?? '') == '3' ? 'selected' : ''; ?>>Semester 3</option>
                                    <option value="4" <?php echo ($student['semester'] ?? '') == '4' ? 'selected' : ''; ?>>Semester 4</option>
                                    <option value="5" <?php echo ($student['semester'] ?? '') == '5' ? 'selected' : ''; ?>>Semester 5</option>
                                    <option value="6" <?php echo ($student['semester'] ?? '') == '6' ? 'selected' : ''; ?>>Semester 6</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-camera"></i>
                                    Profile Picture
                                </label>
                                <div class="image-upload">
                                    <input type="file" name="photo" accept="image/*" onchange="previewImage(this)" id="photoInput">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="upload-text">Click to upload photo</div>
                                    <div class="upload-hint">JPG, PNG or GIF (Max 2MB)</div>
                                    <img id="preview" class="image-preview" 
                                         src="<?php echo !empty($student['photo']) ? 'uploads/' . htmlspecialchars($student['photo']) : '#'; ?>"
                                         style="<?php echo empty($student['photo']) ? 'display:none;' : ''; ?>">
                                    <div class="file-name" id="fileName" style="margin-top: 10px; color: #667eea; font-size: 0.9rem;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="update-btn">
                        <i class="fas fa-save"></i>
                        UPDATE PROFILE
                    </button>
                </form>
            </div>
        </div>
        
        <?php include "includes/footer.php"; ?>
    </div>
    
    <script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        const fileName = document.getElementById('fileName');
        const file = input.files[0];
        
        if (file) {
            // Check file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                input.value = '';
                fileName.textContent = '';
                return;
            }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPG, PNG, GIF)');
                input.value = '';
                fileName.textContent = '';
                return;
            }
            
            // Show file name
            fileName.textContent = 'Selected: ' + file.name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                // Update profile avatar in the card
                const profileAvatar = document.querySelector('.profile-avatar img');
                if (profileAvatar) {
                    profileAvatar.src = e.target.result;
                } else {
                    // If no img element exists, replace the icon with image
                    const profileAvatarDiv = document.querySelector('.profile-avatar');
                    if (profileAvatarDiv) {
                        profileAvatarDiv.innerHTML = '';
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = 'Profile Picture';
                        profileAvatarDiv.appendChild(newImg);
                    }
                }
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            fileName.textContent = '';
        }
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
        
        // Initialize preview with current image if exists
        const currentImage = document.querySelector('img[src^="uploads/"]');
        if (currentImage && currentImage.src.includes('uploads/')) {
            const preview = document.getElementById('preview');
            preview.src = currentImage.src;
            preview.style.display = 'block';
        }
        
        // Show current file name in upload area
        const photoInput = document.getElementById('photoInput');
        if (photoInput) {
            const currentPhoto = '<?php echo !empty($student['photo']) ? $student['photo'] : ''; ?>';
            if (currentPhoto) {
                document.getElementById('fileName').textContent = 'Current: ' + currentPhoto;
            }
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>