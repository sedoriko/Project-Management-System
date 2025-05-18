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

// Handle search and sort
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
}

$sort_direction = isset($_GET['sort']) && strtolower($_GET['sort']) === 'asc' ? 'ASC' : 'DESC';

// Get tasks based on user type
$tasks = [];
if ($user_type == 1) { // Admin
    $query = "SELECT t.*, p.name as project_name 
              FROM task_list t 
              JOIN project_list p ON t.project_id = p.project_id";

    if (!empty($search_term)) {
        $query .= " WHERE t.task_name LIKE ?";
    }

    $query .= " ORDER BY t.task_id $sort_direction";
} else { // Manager or Employee
    $query = "SELECT t.*, p.name as project_name 
              FROM task_list t 
              JOIN project_list p ON t.project_id = p.project_id 
              JOIN project_users pu ON t.project_id = pu.project_id 
              WHERE pu.user_id = $user_id";

    if (!empty($search_term)) {
        $query .= " AND t.task_name LIKE ?";
    }

    $query .= " ORDER BY t.task_id $sort_direction";
}

$stmt = $conn->prepare($query);
if (!empty($search_term)) {
    $search_param = "%{$search_term}%";
    $stmt->bind_param("s", $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
?>

<?php include 'header.php'; ?>

<style>
    body {
        background-image: url('image/BG2.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }
    
    .container-fluid {
        background-color: rgba(255, 255, 255, 0.85);
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .card {
        background-color: rgba(255, 255, 255, 0.9);
    }

    .action-buttons .btn {
        margin-right: 5px;
    }

    .action-buttons .btn:last-child {
        margin-right: 0;
    }
</style>

<div class="container-fluid">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Tasks</h1>
                <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                    <a href="manage_task.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> New Task
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
                                    <th>Task Name</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><?php echo $task['task_id']; ?></td>
                                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                        <td><?php echo htmlspecialchars($task['project_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $task['status'] == 'Completed' ? 'success' : 
                                                     ($task['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                            ?>">
                                                <?php echo $task['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($task['date_created'])); ?></td>
                                        <td>
                                            <div class="action-buttons d-flex">
                                                <a href="view_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                                                    <a href="manage_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($tasks)): ?>
                                    <tr><td colspan="6" class="text-center">No tasks found.</td></tr>
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
