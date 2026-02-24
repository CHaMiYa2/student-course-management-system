<?php
$page_title = 'Instructor Details';
require_once '../../includes/header.php';

$instructor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($instructor_id <= 0) {
    $_SESSION['message'] = 'Invalid instructor ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    
    // Get instructor data
    $stmt = $db->query("SELECT * FROM instructors WHERE instructor_id = ?", [$instructor_id]);
    $instructor = $stmt->fetch();
    
    if (!$instructor) {
        $_SESSION['message'] = 'Instructor not found.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    // Get instructor's courses with enrollment count
    $stmt = $db->query("
        SELECT c.*, COUNT(e.enrollment_id) as enrollment_count
        FROM courses c 
        LEFT JOIN enrollments e ON c.course_id = e.course_id 
        WHERE c.instructor_id = ? 
        GROUP BY c.course_id 
        ORDER BY c.course_code
    ", [$instructor_id]);
    $courses = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Error loading instructor: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">Instructor Details</h1>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?php echo $instructor_id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit Instructor
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Instructors
        </a>
    </div>
</div>

<div class="row">
    <!-- Instructor Information -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chalkboard-teacher"></i> Instructor Information</h3>
            </div>
            <div class="card-body">
                <div class="instructor-info">
                    <div class="info-row">
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                    </div>
                    <div class="info-row">
                        <strong>Email:</strong> 
                        <a href="mailto:<?php echo htmlspecialchars($instructor['email']); ?>">
                            <?php echo htmlspecialchars($instructor['email']); ?>
                        </a>
                    </div>
                    <div class="info-row">
                        <strong>Department:</strong> 
                        <?php echo htmlspecialchars($instructor['department'] ?: 'Not specified'); ?>
                    </div>
                    <div class="info-row">
                        <strong>Hire Date:</strong> 
                        <?php echo date('M j, Y', strtotime($instructor['hire_date'])); ?>
                    </div>
                    <div class="info-row">
                        <strong>Instructor ID:</strong> 
                        <?php echo $instructor['instructor_id']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Course Statistics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Course Statistics</h3>
            </div>
            <div class="card-body">
                <?php
                $total_courses = count($courses);
                $total_enrollments = array_sum(array_column($courses, 'enrollment_count'));
                $avg_enrollments = $total_courses > 0 ? round($total_enrollments / $total_courses, 1) : 0;
                ?>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_courses; ?></div>
                        <div class="stat-label">Total Courses</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_enrollments; ?></div>
                        <div class="stat-label">Total Enrollments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $avg_enrollments; ?></div>
                        <div class="stat-label">Avg. Enrollments</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Courses Table -->
<div class="card mt-4">
    <div class="card-header">
        <h3><i class="fas fa-book"></i> Assigned Courses</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($courses)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Description</th>
                            <th>Enrollments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td>
                                    <?php 
                                    if ($course['description']) {
                                        echo htmlspecialchars(substr($course['description'], 0, 100));
                                        if (strlen($course['description']) > 100) echo '...';
                                    } else {
                                        echo '<em>No description</em>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo $course['enrollment_count']; ?> students
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">No courses assigned to this instructor.</p>
        <?php endif; ?>
    </div>
</div>



<?php require_once '../../includes/footer.php'; ?>
