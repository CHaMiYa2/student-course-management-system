<?php
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
    
    // Check if instructor has assigned courses
    $stmt = $db->query("SELECT COUNT(*) as count FROM courses WHERE instructor_id = ?", [$instructor_id]);
    $course_count = $stmt->fetch()['count'];
    
    // Handle deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            try {
                $db->beginTransaction();
                
                // Unassign courses first (set instructor_id to NULL)
                if ($course_count > 0) {
                    $db->query("UPDATE courses SET instructor_id = NULL WHERE instructor_id = ?", [$instructor_id]);
                }
                
                // Delete instructor
                $db->query("DELETE FROM instructors WHERE instructor_id = ?", [$instructor_id]);
                
                $db->commit();
                
                $_SESSION['message'] = 'Instructor deleted successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                $db->rollback();
                $_SESSION['message'] = 'Error deleting instructor: ' . $e->getMessage();
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
    $_SESSION['message'] = 'Error loading instructor: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">Delete Instructor</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Instructors
    </a>
</div>

<div class="form-container">
    <div class="alert alert-warning">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        <p>Are you sure you want to delete the following instructor?</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h4><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></h4>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($instructor['email']); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($instructor['department'] ?: 'Not specified'); ?></p>
            <p><strong>Hire Date:</strong> <?php echo date('M j, Y', strtotime($instructor['hire_date'])); ?></p>
            
            <?php if ($course_count > 0): ?>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This instructor has <?php echo $course_count; ?> assigned course(s) that will be unassigned.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="confirm" value="yes">
        
        <div class="form-group">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Yes, Delete Instructor
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>



<?php require_once '../../includes/footer.php'; ?>
