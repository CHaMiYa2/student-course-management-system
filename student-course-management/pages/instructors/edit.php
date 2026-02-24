<?php
$page_title = 'Edit Instructor';
require_once '../../includes/header.php';

$errors = [];
$instructor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($instructor_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM instructors WHERE instructor_id = ?", [$instructor_id]);
    $instructor = $stmt->fetch();
    
    if (!$instructor) {
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructor = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'department' => trim($_POST['department'] ?? ''),
        'hire_date' => $_POST['hire_date'] ?? ''
    ];
    
    // Validation
    if (empty($instructor['first_name'])) $errors[] = 'First name is required.';
    if (empty($instructor['last_name'])) $errors[] = 'Last name is required.';
    if (empty($instructor['email'])) $errors[] = 'Email is required.';
    if (!filter_var($instructor['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
    
    if (empty($errors)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM instructors WHERE email = ? AND instructor_id != ?", 
                              [$instructor['email'], $instructor_id]);
            if ($stmt->fetch()['count'] > 0) {
                $errors[] = 'Email already exists.';
            } else {
                $sql = "UPDATE instructors SET first_name = ?, last_name = ?, email = ?, department = ?, 
                        hire_date = ? WHERE instructor_id = ?";
                $db->query($sql, [
                    $instructor['first_name'], $instructor['last_name'], $instructor['email'],
                    $instructor['department'] ?: null, $instructor['hire_date'], $instructor_id
                ]);
                
                $_SESSION['message'] = 'Instructor updated successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Error updating instructor: ' . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Edit Instructor</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Instructors
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
                   value="<?php echo htmlspecialchars($instructor['first_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="last_name" class="form-label">Last Name *</label>
            <input type="text" id="last_name" name="last_name" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['last_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="department" class="form-label">Department</label>
            <input type="text" id="department" name="department" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['department']); ?>">
        </div>
        
        <div class="form-group">
            <label for="hire_date" class="form-label">Hire Date *</label>
            <input type="date" id="hire_date" name="hire_date" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['hire_date']); ?>" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Instructor
            </button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
