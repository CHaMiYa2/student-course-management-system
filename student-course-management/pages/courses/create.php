<?php
$page_title = 'Add New Course';
require_once '../../includes/header.php';

$errors = [];
$course = [
    'course_code' => '',
    'course_name' => '',
    'credits' => '',
    'instructor_id' => '',
    'description' => ''
];

try {
    $db = getDB();
    
    // Get all instructors for dropdown
    $stmt = $db->query("SELECT instructor_id, first_name, last_name, department FROM instructors ORDER BY last_name, first_name");
    $instructors = $stmt->fetchAll();
    
} catch (Exception $e) {
    $errors[] = 'Error loading instructors: ' . $e->getMessage();
    $instructors = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $course = [
            'course_code' => trim($_POST['course_code'] ?? ''),
            'course_name' => trim($_POST['course_name'] ?? ''),
            'credits' => $_POST['credits'] ?? '',
            'instructor_id' => $_POST['instructor_id'] ?? '',
            'description' => trim($_POST['description'] ?? '')
        ];
        
        // Validation
        if (empty($course['course_code'])) {
            $errors[] = 'Course code is required.';
        } elseif (strlen($course['course_code']) > 10) {
            $errors[] = 'Course code must be 10 characters or less.';
        }
        
        if (empty($course['course_name'])) {
            $errors[] = 'Course name is required.';
        } elseif (strlen($course['course_name']) > 100) {
            $errors[] = 'Course name must be 100 characters or less.';
        }
        
        if (empty($course['credits'])) {
            $errors[] = 'Credits are required.';
        } elseif (!is_numeric($course['credits']) || $course['credits'] < 1 || $course['credits'] > 10) {
            $errors[] = 'Credits must be a number between 1 and 10.';
        }
        
        if (!empty($course['instructor_id']) && !is_numeric($course['instructor_id'])) {
            $errors[] = 'Invalid instructor selected.';
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            try {
                // Check if course code already exists
                $stmt = $db->query("SELECT COUNT(*) as count FROM courses WHERE course_code = ?", [$course['course_code']]);
                if ($stmt->fetch()['count'] > 0) {
                    $errors[] = 'A course with this code already exists.';
                } else {
                    // Insert new course
                    $sql = "INSERT INTO courses (course_code, course_name, credits, instructor_id, description) 
                            VALUES (?, ?, ?, ?, ?)";
                    $db->query($sql, [
                        $course['course_code'],
                        $course['course_name'],
                        $course['credits'],
                        $course['instructor_id'] ?: null,
                        $course['description'] ?: null
                    ]);
                    
                    $_SESSION['message'] = 'Course added successfully!';
                    $_SESSION['message_type'] = 'success';
                    header('Location: index.php');
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = 'Error saving course: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Add New Course</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Courses
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times"></i>
        </button>
    </div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" data-validate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label for="course_code" class="form-label">Course Code *</label>
            <input type="text" id="course_code" name="course_code" class="form-control" 
                   value="<?php echo htmlspecialchars($course['course_code']); ?>" 
                   required maxlength="10" placeholder="e.g., CS101">
        </div>
        
        <div class="form-group">
            <label for="course_name" class="form-label">Course Name *</label>
            <input type="text" id="course_name" name="course_name" class="form-control" 
                   value="<?php echo htmlspecialchars($course['course_name']); ?>" 
                   required maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="credits" class="form-label">Credits *</label>
            <input type="number" id="credits" name="credits" class="form-control" 
                   value="<?php echo htmlspecialchars($course['credits']); ?>" 
                   required min="1" max="10">
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
            <textarea id="description" name="description" class="form-control" rows="4" 
                      placeholder="Enter course description..."><?php echo htmlspecialchars($course['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Course
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>



<?php require_once '../../includes/footer.php'; ?>
