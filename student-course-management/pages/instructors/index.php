<?php
$page_title = 'Instructors';
require_once '../../includes/header.php';

try {
    $db = getDB();
    
    // Handle search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where_clause = '';
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE i.first_name LIKE ? OR i.last_name LIKE ? OR i.email LIKE ? OR i.department LIKE ?";
        $search_param = "%$search%";
        $params = [$search_param, $search_param, $search_param, $search_param];
    }
    
    // Get instructors with course count
    $sql = "
        SELECT i.*, 
               COUNT(c.course_id) as course_count
        FROM instructors i 
        LEFT JOIN courses c ON i.instructor_id = c.instructor_id 
        $where_clause
        GROUP BY i.instructor_id 
        ORDER BY i.last_name, i.first_name
    ";
    
    $stmt = $db->query($sql, $params);
    $instructors = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['message'] = "Error loading instructors: " . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    $instructors = [];
}
?>

<div class="page-header">
    <h1 class="page-title">Instructors</h1>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Instructor
    </a>
</div>

<!-- Search Bar -->
<div class="search-container">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search instructors..." 
               value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <button onclick="exportToCSV('instructorsTable', 'instructors.csv')" class="btn btn-secondary">
        <i class="fas fa-download"></i> Export CSV
    </button>
</div>

<!-- Instructors Table -->
<div class="table-container">
    <table class="table" id="instructorsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Hire Date</th>
                <th>Courses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($instructors)): ?>
                <?php foreach ($instructors as $instructor): ?>
                    <tr>
                        <td><?php echo $instructor['instructor_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                        <td><?php echo htmlspecialchars($instructor['department'] ?: '-'); ?></td>
                        <td>
                            <?php echo $instructor['hire_date'] ? date('M j, Y', strtotime($instructor['hire_date'])) : '-'; ?>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $instructor['course_count']; ?> courses
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="view.php?id=<?php echo $instructor['instructor_id']; ?>" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $instructor['instructor_id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $instructor['instructor_id']; ?>" 
                                   class="btn btn-sm btn-danger btn-delete" 
                                   data-confirm="Are you sure you want to delete this instructor? This will unassign all their courses."
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
                        <?php echo empty($search) ? 'No instructors found.' : 'No instructors match your search.'; ?>
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
    const table = document.getElementById('instructorsTable');
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
