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

// Get my courses count
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$myCourses = $stmt->get_result()->fetch_assoc()['cnt'];

// Get total courses count
$totalCourses = $conn->query("SELECT COUNT(*) as cnt FROM courses")->fetch_assoc()['cnt'];

// Get total students count
$totalStudents = $conn->query("SELECT COUNT(*) as cnt FROM students")->fetch_assoc()['cnt'];

// Get chart data - total students registered per course
$chart_data = [];
$res = $conn->query("
    SELECT c.code as course_code, COUNT(e.student_id) as student_count 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    GROUP BY c.id, c.code
    ORDER BY c.code
");

while ($row = $res->fetch_assoc()) {
    $chart_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            font-weight: 300;
        }
        
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 1.8rem 1.5rem;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px 4px 0 0;
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 1rem;
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0.5rem 0;
            line-height: 1;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.95rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        
        .chart-card {
            background: white;
            border-radius: 14px;
            padding: 1.8rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            margin-top: 2rem;
        }
        
        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-title i {
            color: #667eea;
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
            }
            
            .welcome-title {
                font-size: 1.6rem;
            }
            
            .welcome-subtitle {
                font-size: 1rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-header {
                padding: 1.5rem 1rem;
                text-align: center;
            }
            
            .chart-card {
                padding: 1.5rem 1rem;
            }
        }
        
        /* Animation for stats */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .stat-card {
            animation: countUp 0.6s ease-out forwards;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            font-size: 0.85rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1 class="welcome-title">Welcome back!</h1>
            <p class="welcome-subtitle">Here's your academic overview</p>
        </div>
        
        <!-- Statistics Grid -->
        <div class="stat-grid">
            <!-- My Courses Card -->
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-number"><?php echo htmlspecialchars($myCourses); ?></div>
                <p class="stat-label">My Courses</p>
            </div>
            
            <!-- Total Courses Card -->
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-number"><?php echo htmlspecialchars($totalCourses); ?></div>
                <p class="stat-label">Total Courses</p>
            </div>
            
            <!-- Total Students Card -->
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo htmlspecialchars($totalStudents); ?></div>
                <p class="stat-label">Total Students</p>
            </div>
        </div>
        
        <!-- Chart Section -->
        <div class="chart-card">
            <h5 class="chart-title">
                <i class="fas fa-chart-bar"></i>
                Course Registration Distribution
            </h5>
            <canvas id="myChart" height="100"></canvas>
            
            <!-- Course Codes Legend -->
            <div class="chart-legend">
                <?php 
                $colors = [
                    'rgba(102, 126, 234, 1)',    // Blue
                    'rgba(118, 75, 162, 1)',     // Purple
                    'rgba(79, 195, 247, 1)',     // Light Blue
                    'rgba(129, 199, 132, 1)',    // Green
                    'rgba(255, 183, 77, 1)',     // Orange
                    'rgba(240, 98, 146, 1)',     // Pink
                    'rgba(171, 71, 188, 1)',     // Purple
                    'rgba(41, 182, 246, 1)'      // Cyan
                ];
                
                foreach ($chart_data as $index => $course): 
                    $color_index = $index % count($colors);
                ?>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?php echo $colors[$color_index]; ?>;"></span>
                    <span><?php echo htmlspecialchars($course['course_code']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php include "includes/footer.php"; ?>
    </div>

    <script>
        const ctx = document.getElementById('myChart');
        const data = {
            labels: <?php echo json_encode(array_column($chart_data, 'course_code')); ?>,
            datasets: [{
                label: 'Total Students Registered',
                data: <?php echo json_encode(array_column($chart_data, 'student_count')); ?>,
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(118, 75, 162, 0.8)',
                    'rgba(79, 195, 247, 0.8)',
                    'rgba(129, 199, 132, 0.8)',
                    'rgba(255, 183, 77, 0.8)',
                    'rgba(240, 98, 146, 0.8)',
                    'rgba(171, 71, 188, 0.8)',
                    'rgba(41, 182, 246, 0.8)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(118, 75, 162, 1)',
                    'rgba(79, 195, 247, 1)',
                    'rgba(129, 199, 132, 1)',
                    'rgba(255, 183, 77, 1)',
                    'rgba(240, 98, 146, 1)',
                    'rgba(171, 71, 188, 1)',
                    'rgba(41, 182, 246, 1)'
                ],
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        };
        
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y + ' student(s)';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#718096',
                            stepSize: 1,
                            callback: function(value) {
                                return value + ' student(s)';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Number of Students',
                            color: '#4a5568',
                            font: {
                                weight: '600'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                size: 12
                            }
                        },
                        title: {
                            display: true,
                            text: 'Course Code',
                            color: '#4a5568',
                            font: {
                                weight: '600'
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Animate stat numbers
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let startValue = 0;
                const duration = 1500;
                const increment = finalValue / (duration / 16);
                
                const timer = setInterval(() => {
                    startValue += increment;
                    if (startValue >= finalValue) {
                        stat.textContent = finalValue;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(startValue);
                    }
                }, 16);
            });
        });
    </script>
</body>
</html>