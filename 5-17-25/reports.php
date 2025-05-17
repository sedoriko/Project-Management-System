<?php
require_once 'db_connect.php';

// Authentication check - only admin and managers can view reports
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['type'] != 1 && $_SESSION['type'] != 2)) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date';
$sort_order = isset($_GET['sort_order']) && strtolower($_GET['sort_order']) === 'asc' ? 'ASC' : 'DESC';

// Validate sort_by
$valid_sort_columns = ['date', 'project_id'];
if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'date';
}

// Build base query differently based on user type
$query = "SELECT up.*, p.name as project_name, t.task_name, 
          CONCAT(u.firstname, ' ', u.lastname) as user_name 
          FROM user_productivity up 
          JOIN project_list p ON up.project_id = p.project_id 
          LEFT JOIN task_list t ON up.task_id = t.task_id 
          JOIN users u ON up.user_id = u.users_id 
          WHERE 1=1";

// For managers, only show their assigned projects
if ($_SESSION['type'] == 2) { // Manager
    $query .= " AND up.project_id IN (
                SELECT project_id FROM project_list 
                WHERE manager_id = {$_SESSION['user_id']}
              )";
    
    $user_id = 0; // Managers can't filter by user
} 

// Add filters
if ($project_id > 0) {
    $query .= " AND up.project_id = $project_id";
}
if ($user_id > 0 && $_SESSION['type'] == 1) { // Only admin can filter by user
    $query .= " AND up.user_id = $user_id";
}

$query .= " ORDER BY up.$sort_by $sort_order, up.start_time DESC";

// Fetch productivity data
$productivity = [];
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $productivity[] = $row;
}

// Get projects for filter dropdown
$projects = [];
$project_query = "SELECT project_id, name FROM project_list ";
if ($_SESSION['type'] == 2) {
    $project_query .= "WHERE manager_id = {$_SESSION['user_id']} ";
}
$project_query .= "ORDER BY name";
$project_result = $conn->query($project_query);
while ($row = $project_result->fetch_assoc()) {
    $projects[] = $row;
}

// Get users for filter dropdown (only for admin)
$users = [];
if ($_SESSION['type'] == 1) {
    $user_query = "SELECT users_id, firstname, lastname FROM users WHERE type IN (2,3) ORDER BY firstname, lastname";
    $user_result = $conn->query($user_query);
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Calculate totals
$total_hours = 0;
foreach ($productivity as $record) {
    $total_hours += $record['time_rendered'];
}
?>

<?php include 'header.php'; ?>

<style>
    body {
        background-image: url('image/BG2.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        min-height: 100vh;
    }
    
    .container-fluid {
        background-color: rgba(255, 255, 255, 0.88); /* 88% opacity white background */
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.15);
    }
    
    .card {
        background-color: rgba(255, 255, 255, 0.92); /* Slightly more opaque for cards */
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    
    .table {
        background-color: rgba(255, 255, 255, 0.97); /* More opaque for tables */
    }
    
    .alert {
        opacity: 0.96;
    }
    
    /* Make table headers more readable */
    .table thead th {
        background-color: rgba(245, 245, 245, 0.98);
    }
    
    /* Hover effects for better interactivity */
    .table tbody tr:hover {
        background-color: rgba(248, 249, 250, 0.95);
    }
    
    /* Style for sortable headers */
    .table th a {
        transition: color 0.3s ease;
    }
    
    .table th a:hover {
        color: #0d6efd !important;
    }
</style>

<!-- Include Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Productivity Reports</h1>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="project_id" class="form-label">Project</label>
                            <select class="form-select" id="project_id" name="project_id">
                                <option value="0">All Projects</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['project_id']; ?>" 
                                        <?php echo $project_id == $project['project_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($_SESSION['type'] == 1): ?>
                        <div class="col-md-4">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="0">All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['users_id']; ?>" 
                                        <?php echo $user_id == $user['users_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                            <a href="reports.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Productivity Data</h6>
                    <div class="font-weight-bold">
                        Total Hours: <?php echo number_format($total_hours, 2); ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <?php
                                    $new_order = $sort_order === 'ASC' ? 'desc' : 'asc';
                                    $sort_icon = $sort_order === 'ASC' ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill';
                                    ?>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'date', 'sort_order' => $new_order])); ?>" class="text-decoration-none text-dark">
                                            Date
                                            <?php if ($sort_by == 'date'): ?><i class="<?php echo $sort_icon; ?>"></i><?php endif; ?>
                                        </a>
                                    </th>
                                    <th>User</th>
                                    <th>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'project_id', 'sort_order' => $new_order])); ?>" class="text-decoration-none text-dark">
                                            Project
                                            <?php if ($sort_by == 'project_id'): ?><i class="<?php echo $sort_icon; ?>"></i><?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Task</th>
                                    <th>Subject</th>
                                    <th>Time</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productivity as $record): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($record['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['project_name']); ?></td>
                                        <td><?php echo $record['task_name'] ? htmlspecialchars($record['task_name']) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($record['subject']); ?></td>
                                        <td><?php echo date('g:i A', strtotime($record['start_time'])) . ' - ' . date('g:i A', strtotime($record['end_time'])); ?></td>
                                        <td><?php echo number_format($record['time_rendered'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
