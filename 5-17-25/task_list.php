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

// Get tasks based on user type
$tasks = [];
if ($user_type == 1) { // Admin sees all tasks
    $query = "SELECT t.*, p.name as project_name 
              FROM task_list t 
              JOIN project_list p ON t.project_id = p.project_id 
              ORDER BY t.date_created DESC";
} else { // Others see tasks from projects they're assigned to
    $query = "SELECT t.*, p.name as project_name 
              FROM task_list t 
              JOIN project_list p ON t.project_id = p.project_id 
              JOIN project_users pu ON t.project_id = pu.project_id 
              WHERE pu.user_id = $user_id 
              ORDER BY t.date_created DESC";
}

$result = $conn->query($query);
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
        background-color: rgba(255, 255, 255, 0.85); /* 85% opacity white background */
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    
    .card {
        background-color: rgba(255, 255, 255, 0.9); /* Slightly more opaque for the card */
    }
</style>

<div class="container-fluid">
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
                                            <a href="view_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                                                <a href="manage_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
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