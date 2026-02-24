<?php
$page_title = 'Courses';
require_once '../../includes/header.php';

try {
    $db = getDB();
    
    // Handle search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where_clause = '';
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE c.course_code LIKE ? OR c.course_name LIKE ? OR c.description LIKE ? OR i.first_name LIKE ? OR i.last_name LIKE ?";
        $search_param = "%$search%";
        $params = [$search_param, $search_param, $search_param, $search_param, $search_param];
    }
    
    // Get courses with instructor and enrollment count
    $sql = "
        SELECT c.*, 
               i.first_name as instructor_first_name, i.last_name as instructor_last_name,
               COUNT(e.enrollment_id) as enrollment_count
        FROM courses c 
        LEFT JOIN instructors i ON c.instructor_id = i.instructor_id 
        LEFT JOIN enrollments e ON c.course_id = e.course_id 
        $where_clause
        GROUP BY c.course_id 
        ORDER BY c.course_code
    ";
    
    $stmt = $db->query($sql, $params);
    $courses = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = "Error loading courses: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    $courses = [];
}
?>

<div class="page-header">
    <h1 class="page-title">Courses</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Course
    </a>
</div>

<!-- Search Bar -->
<div class="search-container">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search courses..." 
               value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <button onclick="exportToCSV('coursesTable', 'courses.csv')" class="btn btn-secondary">
        <i class="fas fa-download"></i> Export CSV
    </button>
</div>

<!-- Courses Table -->
<div class="table-container">
    <table class="table" id="coursesTable">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Instructor</th>
                <th>Description</th>
                <th>Enrollments</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo $course['credits']; ?></td>
                        <td>
                            <?php 
                            if ($course['instructor_first_name']) {
                                echo htmlspecialchars($course['instructor_first_name'] . ' ' . $course['instructor_last_name']);
                            } else {
                                echo '<em>Not assigned</em>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($course['description']) {
                                echo htmlspecialchars(substr($course['description'], 0, 50));
                                if (strlen($course['description']) > 50) echo '...';
                            } else {
                                echo '<em>No description</em>';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $course['enrollment_count']; ?> students
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="view.php?id=<?php echo $course['course_id']; ?>" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $course['course_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $course['course_id']; ?>" 
                                   class="btn btn-sm btn-danger btn-delete" 
                                   data-confirm="Are you sure you want to delete this course? This will also delete all enrollments."
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
                        <?php echo empty($search) ? 'No courses found.' : 'No courses match your search.'; ?>
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
    const table = document.getElementById('coursesTable');
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
