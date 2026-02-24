<?php
require_once '../../includes/header.php';

$enrollment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($enrollment_id <= 0) {
    $_SESSION['message'] = 'Invalid enrollment ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    
    // Get enrollment data with student and course details
    $stmt = $db->query("
        SELECT e.*, s.first_name, s.last_name, s.email as student_email,
               c.course_code, c.course_name
        FROM enrollments e 
        JOIN students s ON e.student_id = s.student_id 
        JOIN courses c ON e.course_id = c.course_id 
        WHERE e.enrollment_id = ?
    ", [$enrollment_id]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        $_SESSION['message'] = 'Enrollment not found.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }
    
    // Handle deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            try {
                // Delete enrollment
                $db->query("DELETE FROM enrollments WHERE enrollment_id = ?", [$enrollment_id]);
                
                $_SESSION['message'] = 'Enrollment deleted successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                $_SESSION['message'] = 'Error deleting enrollment: ' . $e->getMessage();
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
    $_SESSION['message'] = 'Error loading enrollment: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">Delete Enrollment</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Enrollments
    </a>
</div>

<div class="form-container">
    <div class="alert alert-warning">
        <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        <p>Are you sure you want to delete the following enrollment?</p>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h4>Enrollment Details</h4>
            <p><strong>Student:</strong> <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($enrollment['student_email']); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($enrollment['course_code'] . ' - ' . $enrollment['course_name']); ?></p>
            <p><strong>Enrollment Date:</strong> <?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge badge-<?php 
                    echo $enrollment['status'] === 'completed' ? 'success' : 
                        ($enrollment['status'] === 'dropped' ? 'danger' : 'info'); 
                ?>">
                    <?php echo ucfirst($enrollment['status']); ?>
                </span>
            </p>
            <?php if ($enrollment['grade']): ?>
                <p><strong>Grade:</strong> <?php echo htmlspecialchars($enrollment['grade']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="POST" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="confirm" value="yes">
        
        <div class="form-group">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Yes, Delete Enrollment
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>



<?php require_once '../../includes/footer.php'; ?>
