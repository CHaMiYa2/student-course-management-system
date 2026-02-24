<?php
$page_title = 'Add New Instructor';
require_once '../../includes/header.php';

$errors = [];
$instructor = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'department' => '',
    'hire_date' => date('Y-m-d')
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $instructor = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'hire_date' => $_POST['hire_date'] ?? date('Y-m-d')
        ];
        
        // Validation
        if (empty($instructor['first_name'])) {
            $errors[] = 'First name is required.';
        } elseif (strlen($instructor['first_name']) > 50) {
            $errors[] = 'First name must be 50 characters or less.';
        }
        
        if (empty($instructor['last_name'])) {
            $errors[] = 'Last name is required.';
        } elseif (strlen($instructor['last_name']) > 50) {
            $errors[] = 'Last name must be 50 characters or less.';
        }
        
        if (empty($instructor['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($instructor['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (strlen($instructor['email']) > 100) {
            $errors[] = 'Email must be 100 characters or less.';
        }
        
        if (!empty($instructor['department']) && strlen($instructor['department']) > 100) {
            $errors[] = 'Department must be 100 characters or less.';
        }
        
        if (!empty($instructor['hire_date'])) {
            $hire_date = DateTime::createFromFormat('Y-m-d', $instructor['hire_date']);
            if (!$hire_date || $hire_date->format('Y-m-d') !== $instructor['hire_date']) {
                $errors[] = 'Please enter a valid hire date.';
            }
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            try {
                $db = getDB();
                
                // Check if email already exists
                $stmt = $db->query("SELECT COUNT(*) as count FROM instructors WHERE email = ?", [$instructor['email']]);
                if ($stmt->fetch()['count'] > 0) {
                    $errors[] = 'An instructor with this email address already exists.';
                } else {
                    // Insert new instructor
                    $sql = "INSERT INTO instructors (first_name, last_name, email, department, hire_date) 
                            VALUES (?, ?, ?, ?, ?)";
                    $db->query($sql, [
                        $instructor['first_name'],
                        $instructor['last_name'],
                        $instructor['email'],
                        $instructor['department'] ?: null,
                        $instructor['hire_date']
                    ]);
                    
                    $_SESSION['message'] = 'Instructor added successfully!';
                    $_SESSION['message_type'] = 'success';
                    header('Location: index.php');
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = 'Error saving instructor: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Add New Instructor</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Instructors
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
            <label for="first_name" class="form-label">First Name *</label>
            <input type="text" id="first_name" name="first_name" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['first_name']); ?>" 
                   required maxlength="50">
        </div>
        
        <div class="form-group">
            <label for="last_name" class="form-label">Last Name *</label>
            <input type="text" id="last_name" name="last_name" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['last_name']); ?>" 
                   required maxlength="50">
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['email']); ?>" 
                   required maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="department" class="form-label">Department</label>
            <input type="text" id="department" name="department" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['department']); ?>" 
                   maxlength="100" placeholder="e.g., Computer Science">
        </div>
        
        <div class="form-group">
            <label for="hire_date" class="form-label">Hire Date *</label>
            <input type="date" id="hire_date" name="hire_date" class="form-control" 
                   value="<?php echo htmlspecialchars($instructor['hire_date']); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Instructor
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>



<?php require_once '../../includes/footer.php'; ?>
