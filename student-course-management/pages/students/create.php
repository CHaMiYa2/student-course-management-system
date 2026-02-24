<?php
$page_title = 'Add New Student';
require_once '../../includes/header.php';

$errors = [];
$student = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'date_of_birth' => '',
    'enrollment_date' => date('Y-m-d')
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $student = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'enrollment_date' => $_POST['enrollment_date'] ?? date('Y-m-d')
        ];
        
        // Validation
        if (empty($student['first_name'])) {
            $errors[] = 'First name is required.';
        } elseif (strlen($student['first_name']) > 50) {
            $errors[] = 'First name must be 50 characters or less.';
        }
        
        if (empty($student['last_name'])) {
            $errors[] = 'Last name is required.';
        } elseif (strlen($student['last_name']) > 50) {
            $errors[] = 'Last name must be 50 characters or less.';
        }
        
        if (empty($student['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (strlen($student['email']) > 100) {
            $errors[] = 'Email must be 100 characters or less.';
        }
        
        if (!empty($student['phone']) && strlen($student['phone']) > 15) {
            $errors[] = 'Phone number must be 15 characters or less.';
        }
        
        if (!empty($student['date_of_birth'])) {
            $dob = DateTime::createFromFormat('Y-m-d', $student['date_of_birth']);
            if (!$dob || $dob->format('Y-m-d') !== $student['date_of_birth']) {
                $errors[] = 'Please enter a valid date of birth.';
            }
        }
        
        if (!empty($student['enrollment_date'])) {
            $enrollment_date = DateTime::createFromFormat('Y-m-d', $student['enrollment_date']);
            if (!$enrollment_date || $enrollment_date->format('Y-m-d') !== $student['enrollment_date']) {
                $errors[] = 'Please enter a valid enrollment date.';
            }
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            try {
                $db = getDB();
                
                // Check if email already exists
                $stmt = $db->query("SELECT COUNT(*) as count FROM students WHERE email = ?", [$student['email']]);
                if ($stmt->fetch()['count'] > 0) {
                    $errors[] = 'A student with this email address already exists.';
                } else {
                    // Insert new student
                    $sql = "INSERT INTO students (first_name, last_name, email, phone, date_of_birth, enrollment_date) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $db->query($sql, [
                        $student['first_name'],
                        $student['last_name'],
                        $student['email'],
                        $student['phone'] ?: null,
                        $student['date_of_birth'] ?: null,
                        $student['enrollment_date']
                    ]);
                    
                    $_SESSION['message'] = 'Student added successfully!';
                    $_SESSION['message_type'] = 'success';
                    header('Location: index.php');
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = 'Error saving student: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Add New Student</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Students
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
                   value="<?php echo htmlspecialchars($student['first_name']); ?>" 
                   required maxlength="50">
        </div>
        
        <div class="form-group">
            <label for="last_name" class="form-label">Last Name *</label>
            <input type="text" id="last_name" name="last_name" class="form-control" 
                   value="<?php echo htmlspecialchars($student['last_name']); ?>" 
                   required maxlength="50">
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($student['email']); ?>" 
                   required maxlength="100">
        </div>
        
        <div class="form-group">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-control" 
                   value="<?php echo htmlspecialchars($student['phone']); ?>" 
                   maxlength="15" placeholder="(555) 123-4567">
        </div>
        
        <div class="form-group">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" 
                   value="<?php echo htmlspecialchars($student['date_of_birth']); ?>">
        </div>
        
        <div class="form-group">
            <label for="enrollment_date" class="form-label">Enrollment Date *</label>
            <input type="date" id="enrollment_date" name="enrollment_date" class="form-control" 
                   value="<?php echo htmlspecialchars($student['enrollment_date']); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Student
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>



<?php require_once '../../includes/footer.php'; ?>
