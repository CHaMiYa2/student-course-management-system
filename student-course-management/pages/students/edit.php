<?php
$page_title = 'Edit Student';
require_once '../../includes/header.php';

$errors = [];
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM students WHERE student_id = ?", [$student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? '',
        'enrollment_date' => $_POST['enrollment_date'] ?? ''
    ];
    
    // Validation
    if (empty($student['first_name'])) $errors[] = 'First name is required.';
    if (empty($student['last_name'])) $errors[] = 'Last name is required.';
    if (empty($student['email'])) $errors[] = 'Email is required.';
    if (!filter_var($student['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
    
    if (empty($errors)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM students WHERE email = ? AND student_id != ?", 
                              [$student['email'], $student_id]);
            if ($stmt->fetch()['count'] > 0) {
                $errors[] = 'Email already exists.';
            } else {
                $sql = "UPDATE students SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                        date_of_birth = ?, enrollment_date = ? WHERE student_id = ?";
                $db->query($sql, [
                    $student['first_name'], $student['last_name'], $student['email'],
                    $student['phone'] ?: null, $student['date_of_birth'] ?: null,
                    $student['enrollment_date'], $student_id
                ]);
                
                $_SESSION['message'] = 'Student updated successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Error updating student: ' . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Edit Student</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Students
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
            <label for="first_name" class="form-label">First Name *</label>
            <input type="text" id="first_name" name="first_name" class="form-control" 
                   value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="last_name" class="form-label">Last Name *</label>
            <input type="text" id="last_name" name="last_name" class="form-control" 
                   value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-control" 
                   value="<?php echo htmlspecialchars($student['phone']); ?>">
        </div>
        
        <div class="form-group">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" 
                   value="<?php echo htmlspecialchars($student['date_of_birth']); ?>">
        </div>
        
        <div class="form-group">
            <label for="enrollment_date" class="form-label">Enrollment Date *</label>
            <input type="date" id="enrollment_date" name="enrollment_date" class="form-control" 
                   value="<?php echo htmlspecialchars($student['enrollment_date']); ?>" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Student
            </button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
