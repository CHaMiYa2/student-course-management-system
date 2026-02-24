<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to check if current page is active
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page === $page) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Student Course Management System</title>
    <link rel="stylesheet" href="/student-course-management/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-graduation-cap"></i> Student Course Management</h1>
                </div>
                <nav class="nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="/student-course-management/index.php" class="nav-link <?php echo isActive('index.php'); ?>">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/student-course-management/pages/students/index.php" class="nav-link <?php echo isActive('index.php') && strpos($_SERVER['PHP_SELF'], 'students') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/student-course-management/pages/instructors/index.php" class="nav-link <?php echo isActive('index.php') && strpos($_SERVER['PHP_SELF'], 'instructors') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-chalkboard-teacher"></i> Instructors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/student-course-management/pages/courses/index.php" class="nav-link <?php echo isActive('index.php') && strpos($_SERVER['PHP_SELF'], 'courses') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-book"></i> Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/student-course-management/pages/enrollments/index.php" class="nav-link <?php echo isActive('index.php') && strpos($_SERVER['PHP_SELF'], 'enrollments') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-user-graduate"></i> Enrollments
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                    <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
