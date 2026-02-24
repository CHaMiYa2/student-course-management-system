<?php
$page_title = 'Course Details';
require_once '../../includes/header.php';

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id <= 0) {
    $_SESSION['message'] = 'Invalid course ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    
    // Get course data with instructor info
    $stmt = $db->query("
        SELECT c.*, i.first_name as instructor_first_name, i.last_name as instructor_last_name, i.email as instructor_email
        FROM courses c 
        LEFT JOIN instructors i ON c.instructor_id = i.instructor_id 
        WHERE c.course_id = ?
    ", [$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        $_SESSION['message'] = 'Course not found.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    // Get enrolled students
    $stmt = $db->query("
        SELECT e.*, s.first_name, s.last_name, s.email
        FROM enrollments e 
        JOIN students s ON e.student_id = s.student_id 
        WHERE e.course_id = ? 
        ORDER BY s.last_name, s.first_name
    ", [$course_id]);
    $enrollments = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Error loading course: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">Course Details</h1>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?php echo $course_id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit Course
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
</div>

<div class="row">
    <!-- Course Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-book"></i> Course Information</h3>
            </div>
            <div class="card-body">
                <div class="course-info">
                    <div class="info-row">
                        <strong>Course Code:</strong> 
                        <?php echo htmlspecialchars($course['course_code']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Course Name:</strong> 
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Credits:</strong> 
                        <?php echo $course['credits']; ?>
                    </div>
                    <div class="info-row">
                        <strong>Instructor:</strong> 
                        <?php 
                        if ($course['instructor_first_name']) {
                            echo htmlspecialchars($course['instructor_first_name'] . ' ' . $course['instructor_last_name']);
                            echo '<br><small><a href="mailto:' . htmlspecialchars($course['instructor_email']) . '">' . 
                                 htmlspecialchars($course['instructor_email']) . '</a></small>';
                        } else {
                            echo '<em>Not assigned</em>';
                        }
                        ?>
                    </div>
                    <div class="info-row">
                        <strong>Description:</strong> 
                        <?php echo htmlspecialchars($course['description'] ?: 'No description available'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrollment Statistics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Enrollment Statistics</h3>
            </div>
            <div class="card-body">
                <?php
                $total_enrollments = count($enrollments);
                $completed_enrollments = count(array_filter($enrollments, function($e) { return $e['status'] === 'completed'; }));
                $active_enrollments = count(array_filter($enrollments, function($e) { return $e['status'] === 'enrolled'; }));
                $dropped_enrollments = count(array_filter($enrollments, function($e) { return $e['status'] === 'dropped'; }));
                ?>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_enrollments; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $active_enrollments; ?></div>
                        <div class="stat-label">Currently Enrolled</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $completed_enrollments; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $dropped_enrollments; ?></div>
                        <div class="stat-label">Dropped</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enrolled Students Table -->
<div class="card mt-4">
    <div class="card-header">
        <h3><i class="fas fa-users"></i> Enrolled Students</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($enrollments)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($enrollment['email']); ?>">
                                        <?php echo htmlspecialchars($enrollment['email']); ?>
                                    </a>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $enrollment['status'] === 'completed' ? 'success' : 
                                            ($enrollment['status'] === 'dropped' ? 'danger' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($enrollment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($enrollment['grade']) {
                                        echo '<strong>' . htmlspecialchars($enrollment['grade']) . '</strong>';
                                    } else {
                                        echo '<em>Not graded</em>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No students are currently enrolled in this course.</p>
        <?php endif; ?>
    </div>
</div>



<?php require_once '../../includes/footer.php'; ?>
