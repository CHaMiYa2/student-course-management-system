<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

try {
    $db = getDB();
    
    // Get statistics
    $stats = [];
    
    // Total students
    $stmt = $db->query("SELECT COUNT(*) as count FROM students");
    $stats['students'] = $stmt->fetch()['count'];
    
    // Total instructors
    $stmt = $db->query("SELECT COUNT(*) as count FROM instructors");
    $stats['instructors'] = $stmt->fetch()['count'];
    
    // Total courses
    $stmt = $db->query("SELECT COUNT(*) as count FROM courses");
    $stats['courses'] = $stmt->fetch()['count'];
    
    // Total enrollments
    $stmt = $db->query("SELECT COUNT(*) as count FROM enrollments");
    $stats['enrollments'] = $stmt->fetch()['count'];
    
    // Recent enrollments
    $stmt = $db->query("
        SELECT e.*, s.first_name, s.last_name, c.course_name 
        FROM enrollments e 
        JOIN students s ON e.student_id = s.student_id 
        JOIN courses c ON e.course_id = c.course_id 
        ORDER BY e.enrollment_date DESC 
        LIMIT 5
    ");
    $recent_enrollments = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = "Error loading dashboard: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    $stats = ['students' => 0, 'instructors' => 0, 'courses' => 0, 'enrollments' => 0];
    $recent_enrollments = [];
}
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <div class="d-flex gap-2">
        <a href="pages/students/create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Student
        </a>
        <a href="pages/courses/create.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add Course
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid">
    <div class="dashboard-card">
        <i class="fas fa-users"></i>
        <h3><?php echo $stats['students']; ?></h3>
        <p>Total Students</p>
    </div>
    
    <div class="dashboard-card">
        <i class="fas fa-chalkboard-teacher"></i>
        <h3><?php echo $stats['instructors']; ?></h3>
        <p>Total Instructors</p>
    </div>
    
    <div class="dashboard-card">
        <i class="fas fa-book"></i>
        <h3><?php echo $stats['courses']; ?></h3>
        <p>Total Courses</p>
    </div>
    
    <div class="dashboard-card">
        <i class="fas fa-user-graduate"></i>
        <h3><?php echo $stats['enrollments']; ?></h3>
        <p>Total Enrollments</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="pages/students/index.php" class="btn btn-outline-primary">
                        <i class="fas fa-users"></i> Manage Students
                    </a>
                    <a href="pages/instructors/index.php" class="btn btn-outline-success">
                        <i class="fas fa-chalkboard-teacher"></i> Manage Instructors
                    </a>
                    <a href="pages/courses/index.php" class="btn btn-outline-info">
                        <i class="fas fa-book"></i> Manage Courses
                    </a>
                    <a href="pages/enrollments/index.php" class="btn btn-outline-warning">
                        <i class="fas fa-user-graduate"></i> Manage Enrollments
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clock"></i> Recent Enrollments</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_enrollments)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_enrollments as $enrollment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $enrollment['status'] === 'completed' ? 'success' : ($enrollment['status'] === 'dropped' ? 'danger' : 'info'); ?>">
                                                <?php echo ucfirst($enrollment['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent enrollments</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> System Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Database Status</h5>
                        <p><strong>Connection:</strong> <span class="badge badge-success">Connected</span></p>
                        <p><strong>Database:</strong> student_course_management</p>
                        <p><strong>Tables:</strong> 4 (students, instructors, courses, enrollments)</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Application Info</h5>
                        <p><strong>Version:</strong> 1.0.0</p>
                        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php require_once 'includes/footer.php'; ?>
