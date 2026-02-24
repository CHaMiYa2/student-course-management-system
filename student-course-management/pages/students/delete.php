<?php
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
    
    // Check if student has enrollments
    $stmt = $db->query("SELECT COUNT(*) as count FROM enrollments WHERE student_id = ?", [$student_id]);
    $enrollment_count = $stmt->fetch()['count'];
    
    // Handle deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            try {
                $db->beginTransaction();
                
                // Delete enrollments first (due to foreign key constraint)
                if ($enrollment_count > 0) {
                    $db->query("DELETE FROM enrollments WHERE student_id = ?", [$student_id]);
                }
                
                // Delete student
                $db->query("DELETE FROM students WHERE student_id = ?", [$student_id]);
                
                $db->commit();
                
                $_SESSION['message'] = 'Student deleted successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                $db->rollback();
                $_SESSION['message'] = 'Error deleting student: ' . $e->getMessage();
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
    $_SESSION['message'] = 'Error loading student: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">Delete Student</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
</div>

<div class="form-container">
    <div class="alert alert-warning">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        <p>Are you sure you want to delete the following student?</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h4><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone'] ?: 'Not provided'); ?></p>
            <p><strong>Enrollment Date:</strong> <?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?></p>
            
            <?php if ($enrollment_count > 0): ?>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This student has <?php echo $enrollment_count; ?> enrollment(s) that will also be deleted.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="confirm" value="yes">
        
        <div class="form-group">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Yes, Delete Student
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>



<?php require_once '../../includes/footer.php'; ?>
