<?php
$page_title = 'Student Details';
require_once '../../includes/header.php';

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id <= 0) {
    $_SESSION['message'] = 'Invalid student ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    
    // Get student data
    $stmt = $db->query("SELECT * FROM students WHERE student_id = ?", [$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        $_SESSION['message'] = 'Student not found.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    // Get student enrollments with course and instructor details
    $stmt = $db->query("
        SELECT e.*, c.course_name, c.course_code, c.credits, 
               i.first_name as instructor_first_name, i.last_name as instructor_last_name
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.course_id 
        LEFT JOIN instructors i ON c.instructor_id = i.instructor_id 
        WHERE e.student_id = ? 
        ORDER BY e.enrollment_date DESC
    ", [$student_id]);
    $enrollments = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Error loading student: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">Student Details</h1>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?php echo $student_id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit Student
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>
</div>

<div class="row">
    <!-- Student Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user"></i> Student Information</h3>
            </div>
            <div class="card-body">
                <div class="student-info">
                    <div class="info-row">
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Email:</strong> 
                        <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>">
                            <?php echo htmlspecialchars($student['email']); ?>
                        </a>
                    </div>
                    <div class="info-row">
                        <strong>Phone:</strong> 
                        <?php echo htmlspecialchars($student['phone'] ?: 'Not provided'); ?>
                    </div>
                    <div class="info-row">
                        <strong>Date of Birth:</strong> 
                        <?php echo $student['date_of_birth'] ? date('M j, Y', strtotime($student['date_of_birth'])) : 'Not provided'; ?>
                    </div>
                    <div class="info-row">
                        <strong>Enrollment Date:</strong> 
                        <?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?>
                    </div>
                    <div class="info-row">
                        <strong>Student ID:</strong> 
                        <?php echo $student['student_id']; ?>
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
                $completed_courses = count(array_filter($enrollments, function($e) { return $e['status'] === 'completed'; }));
                $active_enrollments = count(array_filter($enrollments, function($e) { return $e['status'] === 'enrolled'; }));
                $dropped_courses = count(array_filter($enrollments, function($e) { return $e['status'] === 'dropped'; }));
                ?>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_enrollments; ?></div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $completed_courses; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $active_enrollments; ?></div>
                        <div class="stat-label">Currently Enrolled</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $dropped_courses; ?></div>
                        <div class="stat-label">Dropped</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enrollments Table -->
<div class="card mt-4">
    <div class="card-header">
        <h3><i class="fas fa-graduation-cap"></i> Course Enrollments</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($enrollments)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Instructor</th>
                            <th>Credits</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($enrollment['course_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                                <td>
                                    <?php 
                                    if ($enrollment['instructor_first_name']) {
                                        echo htmlspecialchars($enrollment['instructor_first_name'] . ' ' . $enrollment['instructor_last_name']);
                                    } else {
                                        echo '<em>Not assigned</em>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $enrollment['credits']; ?></td>
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
            <p class="text-muted">No course enrollments found for this student.</p>
        <?php endif; ?>
    </div>
</div>



<?php require_once '../../includes/footer.php'; ?>
