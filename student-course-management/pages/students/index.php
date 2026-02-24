<?php
$page_title = 'Students';
require_once '../../includes/header.php';

try {
    $db = getDB();
    
    // Handle search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where_clause = '';
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
        $search_param = "%$search%";
        $params = [$search_param, $search_param, $search_param, $search_param];
    }
    
    // Get students with enrollment count
    $sql = "
        SELECT s.*, 
               COUNT(e.enrollment_id) as enrollment_count,
               COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_courses
        FROM students s 
        LEFT JOIN enrollments e ON s.student_id = e.student_id 
        $where_clause
        GROUP BY s.student_id 
        ORDER BY s.last_name, s.first_name
    ";
    
    $stmt = $db->query($sql, $params);
    $students = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = "Error loading students: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    $students = [];
}
?>

<div class="page-header">
    <h1 class="page-title">Students</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Student
    </a>
</div>

<!-- Search Bar -->
<div class="search-container">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search students..." 
               value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <button onclick="exportToCSV('studentsTable', 'students.csv')" class="btn btn-secondary">
        <i class="fas fa-download"></i> Export CSV
    </button>
</div>

<!-- Students Table -->
<div class="table-container">
    <table class="table" id="studentsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Date of Birth</th>
                <th>Enrollment Date</th>
                <th>Courses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['student_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['phone'] ?: '-'); ?></td>
                        <td>
                            <?php echo $student['date_of_birth'] ? date('M j, Y', strtotime($student['date_of_birth'])) : '-'; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?></td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $student['enrollment_count']; ?> enrolled
                            </span>
                            <?php if ($student['completed_courses'] > 0): ?>
                                <span class="badge badge-success">
                                    <?php echo $student['completed_courses']; ?> completed
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="view.php?id=<?php echo $student['student_id']; ?>" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $student['student_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $student['student_id']; ?>" 
                                   class="btn btn-sm btn-danger btn-delete" 
                                   data-confirm="Are you sure you want to delete this student? This will also delete all their enrollments."
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">
                        <?php echo empty($search) ? 'No students found.' : 'No students match your search.'; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById('studentsTable');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Export to CSV function
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        cols.forEach(col => {
            // Skip action columns
            if (col.querySelector('.btn')) return;
            
            const text = col.textContent.trim();
            const escaped = text.replace(/"/g, '""');
            rowData.push(text.includes(',') ? `"${escaped}"` : escaped);
        });
        
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>
