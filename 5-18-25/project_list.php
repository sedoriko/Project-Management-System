<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['type'];

// Check if search form was submitted
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
}

// Check if sort direction is specified
$sort_direction = isset($_GET['sort']) && strtolower($_GET['sort']) === 'desc' ? 'DESC' : 'ASC';

// Get projects based on user type and search term
$projects = [];
$query = '';

if ($user_type == 1) { // Admin sees all projects
    $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) as manager_name 
              FROM project_list p 
              JOIN users u ON p.manager_id = u.users_id";
              
    if (!empty($search_term)) {
        $query .= " WHERE p.name LIKE ?";
    }
    
    $query .= " ORDER BY p.project_id $sort_direction";
    
} elseif ($user_type == 2) { // Manager sees projects they manage or are assigned to
    $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) as manager_name 
              FROM project_list p 
              JOIN users u ON p.manager_id = u.users_id 
              WHERE (p.manager_id = $user_id OR p.project_id IN (
                  SELECT project_id FROM project_users WHERE user_id = $user_id
              ))";
              
    if (!empty($search_term)) {
        $query .= " AND p.name LIKE ?";
    }
    
    $query .= " ORDER BY p.project_id $sort_direction";
    
} else { // Employee sees only assigned projects
    $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) as manager_name 
              FROM project_list p 
              JOIN users u ON p.manager_id = u.users_id 
              JOIN project_users pu ON p.project_id = pu.project_id 
              WHERE pu.user_id = $user_id";
              
    if (!empty($search_term)) {
        $query .= " AND p.name LIKE ?";
    }
    
    $query .= " ORDER BY p.project_id $sort_direction";
}

$stmt = $conn->prepare($query);

if (!empty($search_term)) {
    $search_param = "%{$search_term}%";
    $stmt->bind_param("s", $search_param);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}
?>

<?php include 'header.php'; ?>

<style>
    /* Background image styles */
    body {
        background-image: url('/project_management/image/BG2.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        min-height: 100vh;
    }
    
    /* Semi-transparent container */
    .container-fluid {
        background-color: rgba(255, 255, 255, 0.85); /* 75% white transparency */
        border-radius: 15px;
        padding: 20px;
        margin-top: 20px;
        backdrop-filter: blur(5px); /* Optional: adds slight blur effect */
    }
    
    /* Semi-transparent cards */
    .card {
        background-color: rgba(255, 255, 255, 0.85); /* 65% white transparency */
        border: none;
    }
    
    /* Card header transparency */
    .card-header {
        background-color: rgba(255, 255, 255, 0.85); /* Slightly less transparent */
    }
    
    /* List group items */
    .list-group-item {
        background-color: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    
    .card-body .h5 {
        font-size: 2rem !important;
    }
    
    .list-group-item small b {
        font-weight: 600 !important;
    }
    .action-buttons .btn {
        margin-right: 5px;
    }

    .action-buttons .btn:last-child {
        margin-right: 0;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Projects</h1>
                <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                    <a href="new_project.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> New Project
                    </a>
                <?php endif; ?>
            </div>

            <!-- Search and Sort Bar -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="search-sort-container d-flex">
                        <form method="GET" action="" class="input-group search-form" style="max-width: 300px;">
                            <input type="text" name="search" class="form-control" placeholder="Search..." 
                                value="<?php echo htmlspecialchars($search_term); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        <a href="?search=<?php echo urlencode($search_term); ?>&sort=<?php echo $sort_direction === 'ASC' ? 'desc' : 'asc'; ?>" 
                        class="btn btn-outline-secondary ml-2" title="Sort">
                            <i class="fas fa-sort-numeric-<?php echo $sort_direction === 'ASC' ? 'down' : 'up'; ?>"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Project Name</th>
                                    <th>Manager</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($projects)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No projects found<?php echo !empty($search_term) ? ' matching your search' : ''; ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><?php echo $project['project_id']; ?></td>
                                            <td><?php echo htmlspecialchars($project['name']); ?></td>
                                            <td><?php echo htmlspecialchars($project['manager_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($project['start_date'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($project['end_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $project['status'] == 'Completed' ? 'success' : 
                                                         ($project['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                                ?>">
                                                    <?php echo $project['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons d-flex">
                                                    <a href="view_project.php?id=<?php echo $project['project_id']; ?>" class="btn btn-info btn-sm mr-2">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($_SESSION['type'] == 1 || ($_SESSION['type'] == 2 && $project['manager_id'] == $_SESSION['user_id'])): ?>
                                                        <a href="edit_project.php?id=<?php echo $project['project_id']; ?>" class="btn btn-warning btn-sm mr-2">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete_project.php?id=<?php echo $project['project_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>