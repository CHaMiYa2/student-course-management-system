<?php
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
    
    // Get course data
    $stmt = $db->query("SELECT * FROM courses WHERE course_id = ?", [$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        $_SESSION['message'] = 'Course not found.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    // Check if course has enrollments
    $stmt = $db->query("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?", [$course_id]);
    $enrollment_count = $stmt->fetch()['count'];
    
    // Handle deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            try {
                $db->beginTransaction();
                
                // Delete enrollments first (due to foreign key constraint)
                if ($enrollment_count > 0) {
                    $db->query("DELETE FROM enrollments WHERE course_id = ?", [$course_id]);
                }
                
                // Delete course
                $db->query("DELETE FROM courses WHERE course_id = ?", [$course_id]);
                
                $db->commit();
                
                $_SESSION['message'] = 'Course deleted successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                $db->rollback();
                $_SESSION['message'] = 'Error deleting course: ' . $e->getMessage();
                $_SESSION['message_type'] = 'error';
                header('Location: index.php');
                exit;
            }
        } else {
            // User cancelled
            header('Location: index.php');
            exit;
        }
    }
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Error loading course: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">Delete Course</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
</div>

<div class="form-container">
    <div class="alert alert-warning">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        <p>Are you sure you want to delete the following course?</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h4><?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?></h4>
            <p><strong>Credits:</strong> <?php echo $course['credits']; ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description'] ?: 'No description'); ?></p>
            
            <?php if ($enrollment_count > 0): ?>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This course has <?php echo $enrollment_count; ?> enrollment(s) that will also be deleted.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="confirm" value="yes">
        
        <div class="form-group">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Yes, Delete Course
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>



<?php require_once '../../includes/footer.php'; ?>
