<?php
$page_title = 'Edit Course';
require_once '../../includes/header.php';

$errors = [];
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    
    // Get course data
    $stmt = $db->query("SELECT * FROM courses WHERE course_id = ?", [$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: index.php');
        exit;
    }
    
    // Get all instructors for dropdown
    $stmt = $db->query("SELECT instructor_id, first_name, last_name, department FROM instructors ORDER BY last_name, first_name");
    $instructors = $stmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = [
        'course_code' => trim($_POST['course_code'] ?? ''),
        'course_name' => trim($_POST['course_name'] ?? ''),
        'credits' => $_POST['credits'] ?? '',
        'instructor_id' => $_POST['instructor_id'] ?? '',
        'description' => trim($_POST['description'] ?? '')
    ];
    
    // Validation
    if (empty($course['course_code'])) $errors[] = 'Course code is required.';
    if (empty($course['course_name'])) $errors[] = 'Course name is required.';
    if (empty($course['credits'])) $errors[] = 'Credits are required.';
    if (!is_numeric($course['credits']) || $course['credits'] < 1 || $course['credits'] > 10) {
        $errors[] = 'Credits must be a number between 1 and 10.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM courses WHERE course_code = ? AND course_id != ?", 
                              [$course['course_code'], $course_id]);
            if ($stmt->fetch()['count'] > 0) {
                $errors[] = 'Course code already exists.';
            } else {
                $sql = "UPDATE courses SET course_code = ?, course_name = ?, credits = ?, instructor_id = ?, 
                        description = ? WHERE course_id = ?";
                $db->query($sql, [
                    $course['course_code'], $course['course_name'], $course['credits'],
                    $course['instructor_id'] ?: null, $course['description'] ?: null, $course_id
                ]);
                
                $_SESSION['message'] = 'Course updated successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Error updating course: ' . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Edit Course</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Courses
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
            <label for="course_code" class="form-label">Course Code *</label>
            <input type="text" id="course_code" name="course_code" class="form-control" 
                   value="<?php echo htmlspecialchars($course['course_code']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="course_name" class="form-label">Course Name *</label>
            <input type="text" id="course_name" name="course_name" class="form-control" 
                   value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="credits" class="form-label">Credits *</label>
            <input type="number" id="credits" name="credits" class="form-control" 
                   value="<?php echo htmlspecialchars($course['credits']); ?>" required min="1" max="10">
        </div>
        
        <div class="form-group">
            <label for="instructor_id" class="form-label">Instructor</label>
            <select id="instructor_id" name="instructor_id" class="form-control">
                <option value="">Select an instructor (optional)</option>
                <?php foreach ($instructors as $instructor): ?>
                    <option value="<?php echo $instructor['instructor_id']; ?>" 
                            <?php echo $course['instructor_id'] == $instructor['instructor_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name'] . 
                                                   ' (' . $instructor['department'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($course['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Course
            </button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
