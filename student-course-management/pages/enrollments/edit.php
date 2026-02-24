<?php
$page_title = 'Edit Enrollment';
require_once '../../includes/header.php';

$errors = [];
$enrollment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($enrollment_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    
    // Get enrollment data
    $stmt = $db->query("SELECT * FROM enrollments WHERE enrollment_id = ?", [$enrollment_id]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        header('Location: index.php');
        exit;
    }
    
    // Get all students for dropdown
    $stmt = $db->query("SELECT student_id, first_name, last_name, email FROM students ORDER BY last_name, first_name");
    $students = $stmt->fetchAll();
    
    // Get all courses for dropdown
    $stmt = $db->query("SELECT course_id, course_code, course_name, credits FROM courses ORDER BY course_code");
    $courses = $stmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollment = [
        'student_id' => $_POST['student_id'] ?? '',
        'course_id' => $_POST['course_id'] ?? '',
        'enrollment_date' => $_POST['enrollment_date'] ?? '',
        'status' => $_POST['status'] ?? '',
        'grade' => trim($_POST['grade'] ?? '')
    ];
    
    // Validation
    if (empty($enrollment['student_id'])) $errors[] = 'Student is required.';
    if (empty($enrollment['course_id'])) $errors[] = 'Course is required.';
    if (empty($enrollment['enrollment_date'])) $errors[] = 'Enrollment date is required.';
    if (!in_array($enrollment['status'], ['enrolled', 'completed', 'dropped'])) {
        $errors[] = 'Invalid status selected.';
    }
    
    if (!empty($enrollment['grade']) && !preg_match('/^[A-F][+-]?$/', $enrollment['grade'])) {
        $errors[] = 'Grade must be a valid letter grade (A, B, C, D, F with optional + or -).';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND course_id = ? AND enrollment_id != ?", 
                              [$enrollment['student_id'], $enrollment['course_id'], $enrollment_id]);
            if ($stmt->fetch()['count'] > 0) {
                $errors[] = 'This student is already enrolled in this course.';
            } else {
                $sql = "UPDATE enrollments SET student_id = ?, course_id = ?, enrollment_date = ?, status = ?, grade = ? 
                        WHERE enrollment_id = ?";
                $db->query($sql, [
                    $enrollment['student_id'], $enrollment['course_id'], $enrollment['enrollment_date'],
                    $enrollment['status'], $enrollment['grade'] ?: null, $enrollment_id
                ]);
                
                $_SESSION['message'] = 'Enrollment updated successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Error updating enrollment: ' . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Edit Enrollment</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Enrollments
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
            <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" data-validate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label for="student_id" class="form-label">Student *</label>
            <select id="student_id" name="student_id" class="form-control" required>
                <option value="">Select a student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['student_id']; ?>" 
                            <?php echo $enrollment['student_id'] == $student['student_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . 
                                                   ' (' . $student['email'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="course_id" class="form-label">Course *</label>
            <select id="course_id" name="course_id" class="form-control" required>
                <option value="">Select a course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_id']; ?>" 
                            <?php echo $enrollment['course_id'] == $course['course_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name'] . 
                                                   ' (' . $course['credits'] . ' credits)'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="enrollment_date" class="form-label">Enrollment Date *</label>
            <input type="date" id="enrollment_date" name="enrollment_date" class="form-control" 
                   value="<?php echo htmlspecialchars($enrollment['enrollment_date']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="status" class="form-label">Status *</label>
            <select id="status" name="status" class="form-control" required>
                <option value="enrolled" <?php echo $enrollment['status'] === 'enrolled' ? 'selected' : ''; ?>>Enrolled</option>
                <option value="completed" <?php echo $enrollment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="dropped" <?php echo $enrollment['status'] === 'dropped' ? 'selected' : ''; ?>>Dropped</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="grade" class="form-label">Grade</label>
            <input type="text" id="grade" name="grade" class="form-control" 
                   value="<?php echo htmlspecialchars($enrollment['grade']); ?>" 
                   placeholder="A, B+, C-, etc." maxlength="2">
            <small class="form-text">Leave blank if not graded yet. Use format: A, B+, C-, D, F</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Enrollment
            </button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
