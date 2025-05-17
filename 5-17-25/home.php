<?php
require_once 'db_connect.php';



// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Sorting parameters
$project_sort = isset($_GET['project_sort']) ? $_GET['project_sort'] : 'date_created_desc';
$task_sort = isset($_GET['task_sort']) ? $_GET['task_sort'] : 'date_created_desc';

// Parse sorting parameters for projects
$project_order_by = 'p.date_created';
$project_order_dir = 'DESC';
if ($project_sort == 'id_asc') {
    $project_order_by = 'p.project_id';
    $project_order_dir = 'ASC';
} elseif ($project_sort == 'id_desc') {
    $project_order_by = 'p.project_id';
    $project_order_dir = 'DESC';
} elseif ($project_sort == 'date_created_asc') {
    $project_order_by = 'p.date_created';
    $project_order_dir = 'ASC';
}

// Parse sorting parameters for tasks
$task_order_by = 't.date_created';
$task_order_dir = 'DESC';
if ($task_sort == 'id_asc') {
    $task_order_by = 't.task_id';
    $task_order_dir = 'ASC';
} elseif ($task_sort == 'id_desc') {
    $task_order_by = 't.task_id';
    $task_order_dir = 'DESC';
} elseif ($task_sort == 'date_created_asc') {
    $task_order_by = 't.date_created';
    $task_order_dir = 'ASC';
}

// Get user's projects
$projects = [];
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['type'];

if ($user_type == 1) { // Admin sees all projects
    $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) as manager_name 
              FROM project_list p 
              JOIN users u ON p.manager_id = u.users_id 
              ORDER BY $project_order_by $project_order_dir 
              LIMIT 50";
} else {
    $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) as manager_name 
              FROM project_list p 
              JOIN users u ON p.manager_id = u.users_id 
              JOIN project_users pu ON p.project_id = pu.project_id 
              WHERE pu.user_id = $user_id 
              ORDER BY $project_order_by $project_order_dir 
              LIMIT 50";
}

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

// Get user's tasks
$tasks = [];
if ($user_type == 1) { // Admin sees all tasks
    $task_query = "SELECT t.*, p.name as project_name 
                   FROM task_list t 
                   JOIN project_list p ON t.project_id = p.project_id 
                   ORDER BY $task_order_by $task_order_dir 
                   LIMIT 50";
} else {
    $task_query = "SELECT t.*, p.name as project_name 
                   FROM task_list t 
                   JOIN project_list p ON t.project_id = p.project_id 
                   JOIN project_users pu ON p.project_id = pu.project_id 
                   WHERE pu.user_id = $user_id AND t.status != 'Completed' 
                   ORDER BY $task_order_by $task_order_dir 
                   LIMIT 50";
}

$task_result = $conn->query($task_query);
while ($row = $task_result->fetch_assoc()) {
    $tasks[] = $row;
}
?>

<?php include 'header.php'; ?>

<style>
    /* Background image styles */
    body {
        background-image: url('/project_management/image/BG.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        min-height: 100vh;
    }
    
    /* Semi-transparent container */
    .container-fluid {
        background-color: rgba(255, 255, 255, 0.60); /* 75% white transparency */
        border-radius: 15px;
        padding: 20px;
        margin-top: 20px;
        backdrop-filter: blur(5px); /* Optional: adds slight blur effect */
    }
    
    /* Semi-transparent cards */
    .card {
        background-color: rgba(255, 255, 255, 0.65); /* 65% white transparency */
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
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Home</h1>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Projects Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Projects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $count_query = $user_type == 1 
                                        ? "SELECT COUNT(*) FROM project_list" 
                                        : "SELECT COUNT(*) FROM project_users WHERE user_id = $user_id";
                                    $count_result = $conn->query($count_query);
                                    echo $count_result->fetch_row()[0];
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $task_count_query = $user_type == 1
                                        ? "SELECT COUNT(*) FROM task_list WHERE status != 'Completed'"
                                        : "SELECT COUNT(*) FROM task_list t 
                                           JOIN project_users pu ON t.project_id = pu.project_id 
                                           WHERE pu.user_id = $user_id AND t.status != 'Completed'";
                                    $task_count_result = $conn->query($task_count_query);
                                    echo $task_count_result->fetch_row()[0];
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Row -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Projects Progress</h6>
                    <?php if ($user_type == 1): ?>
                        <?php
                            $next_project_sort = ($project_sort == 'id_asc') ? 'id_desc' : 'id_asc';
                            $project_sort_icon = ($project_sort == 'id_asc') ? 'fa-sort-numeric-down' : 'fa-sort-numeric-up';
                        ?>
                        <a href="?project_sort=<?= $next_project_sort ?>&task_sort=<?= $task_sort ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas <?= $project_sort_icon ?>"></i> Sort
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($projects) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($projects as $project): ?>
                                <a href="view_project.php?id=<?php echo $project['project_id']; ?>" 
                                   class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($project['name']); ?></h5>
                                        <small class="text-<?php 
                                            echo $project['status'] == 'Completed' ? 'success' : 
                                                 ($project['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                        ?>">
                                            <?php echo $project['status']; ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>...</p>
                                    <small>Manager: <?php echo htmlspecialchars($project['manager_name']); ?></small>
                                    <small class="d-block mt-1">ID: <?php echo $project['project_id']; ?> | <?php echo date('M d, Y', strtotime($project['date_created'])); ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <a href="project_list.php" class="btn btn-primary btn-block mt-3">View All Projects</a>
                    <?php else: ?>
                        <p>No projects found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Pending Tasks</h6>
                    <?php if ($user_type == 1): ?>
                        <?php
                            $next_task_sort = ($task_sort == 'id_asc') ? 'id_desc' : 'id_asc';
                            $task_sort_icon = ($task_sort == 'id_asc') ? 'fa-sort-numeric-down' : 'fa-sort-numeric-up';
                        ?>
                        <a href="?task_sort=<?= $next_task_sort ?>&project_sort=<?= $project_sort ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas <?= $task_sort_icon ?>"></i> Sort
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($tasks) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($tasks as $task): ?>
                                <a href="view_task.php?id=<?php echo $task['task_id']; ?>" 
                                   class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($task['task_name']); ?></h5>
                                        <small class="text-<?php 
                                            echo $task['status'] == 'Completed' ? 'success' : 
                                                 ($task['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                        ?>">
                                            <?php echo $task['status']; ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">Project: <?php echo htmlspecialchars($task['project_name']); ?></p>
                                    <small>ID: <?php echo $task['task_id']; ?> | Created: <?php echo date('M d, Y', strtotime($task['date_created'])); ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <a href="task_list.php" class="btn btn-primary btn-block mt-3">View All Tasks</a>
                    <?php else: ?>
                        <p>No pending tasks found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
