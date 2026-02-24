<?php
$page_title = 'Enrollments';
require_once '../../includes/header.php';

try {
    $db = getDB();
    
    // Handle search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where_clause = '';
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ? OR c.course_code LIKE ? OR c.course_name LIKE ?";
        $search_param = "%$search%";
        $params = [$search_param, $search_param, $search_param, $search_param, $search_param];
    }
    
    // Get enrollments with student and course details
    $sql = "
        SELECT e.*, 
               s.first_name, s.last_name, s.email as student_email,
               c.course_code, c.course_name, c.credits,
               i.first_name as instructor_first_name, i.last_name as instructor_last_name
        FROM enrollments e 
        JOIN students s ON e.student_id = s.student_id 
        JOIN courses c ON e.course_id = c.course_id 
        LEFT JOIN instructors i ON c.instructor_id = i.instructor_id 
        $where_clause
        ORDER BY e.enrollment_date DESC
    ";
    
    $stmt = $db->query($sql, $params);
    $enrollments = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = "Error loading enrollments: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    $enrollments = [];
}
?>

<div class="page-header">
    <h1 class="page-title">Enrollments</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Enrollment
    </a>
</div>

<!-- Search Bar -->
<div class="search-container">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search enrollments..." 
               value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <button onclick="exportToCSV('enrollmentsTable', 'enrollments.csv')" class="btn btn-secondary">
        <i class="fas fa-download"></i> Export CSV
    </button>
</div>

<!-- Enrollments Table -->
<div class="table-container">
    <table class="table" id="enrollmentsTable">
        <thead>
            <tr>
                <th>Student</th>
                <th>Course</th>
                <th>Instructor</th>
                <th>Enrollment Date</th>
                <th>Status</th>
                <th>Grade</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($enrollments)): ?>
                <?php foreach ($enrollments as $enrollment): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                <br><small><?php echo htmlspecialchars($enrollment['student_email']); ?></small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($enrollment['course_code'] . ' - ' . $enrollment['course_name']); ?></strong>
                                <br><small><?php echo $enrollment['credits']; ?> credits</small>
                            </div>
                        </td>
                        <td>
                            <?php 
                            if ($enrollment['instructor_first_name']) {
                                echo htmlspecialchars($enrollment['instructor_first_name'] . ' ' . $enrollment['instructor_last_name']);
                            } else {
                                echo '<em>Not assigned</em>';
                            }
                            ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $enrollment['status'] === 'completed' ? 'success' : 
                                    ($enrollment['status'] === 'dropped' ? 'danger' : 'info'); 
                            ?>">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if ($enrollment['grade']) {
                                echo '<strong>' . htmlspecialchars($enrollment['grade']) . '</strong>';
                            } else {
                                echo '<em>Not graded</em>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="edit.php?id=<?php echo $enrollment['enrollment_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $enrollment['enrollment_id']; ?>" 
                                   class="btn btn-sm btn-danger btn-delete" 
                                   data-confirm="Are you sure you want to delete this enrollment?"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">
                        <?php echo empty($search) ? 'No enrollments found.' : 'No enrollments match your search.'; ?>
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
    const table = document.getElementById('enrollmentsTable');
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
