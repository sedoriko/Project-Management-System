<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($task_id == 0) {
    header("Location: task_list.php");
    exit();
}

// Get task details
$task = null;
$stmt = $conn->prepare("SELECT t.*, p.name as project_name 
                       FROM task_list t 
                       JOIN project_list p ON t.project_id = p.project_id 
                       WHERE t.task_id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if (!$task) {
    header("Location: task_list.php");
    exit();
}

// Check if current user has access to this task
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['type'];

if ($user_type != 1) {
    // Check if user is assigned to the project
    $stmt = $conn->prepare("SELECT 1 FROM project_users WHERE project_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task['project_id'], $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: task_list.php");
        exit();
    }
}

// Get task productivity
$productivity = [];
$prod_query = "SELECT up.*, CONCAT(u.firstname, ' ', u.lastname) as user_name 
               FROM user_productivity up 
               JOIN users u ON up.user_id = u.users_id 
               WHERE up.task_id = $task_id 
               ORDER BY up.date DESC, up.start_time DESC";
$prod_result = $conn->query($prod_query);
while ($row = $prod_result->fetch_assoc()) {
    $productivity[] = $row;
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
    }
    .container-fluid {
        background-color: rgba(255, 255, 255, 0.85); /* 85% opacity white */
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .card {
        background-color: rgba(255, 255, 255, 0.9); /* Slightly more opaque cards */
        border-radius: 8px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Task Details</h1>
                <div>
                    <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                        <a href="manage_task.php?id=<?php echo $task_id; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Task
                        </a>
                    <?php endif; ?>
                    <a href="task_list.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Tasks
                    </a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Task Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><?php echo htmlspecialchars($task['task_name']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                            
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-<?php 
                                    echo $task['status'] == 'Completed' ? 'success' : 
                                         ($task['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                ?>">
                                    <?php echo $task['status']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Project:</strong>
                                <a href="view_project.php?id=<?php echo $task['project_id']; ?>">
                                    <?php echo htmlspecialchars($task['project_name']); ?>
                                </a>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Created:</strong>
                                <?php echo date('M d, Y H:i', strtotime($task['date_created'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Productivity Records</h6>
                    <div>
                        <span class="badge bg-primary"><?php echo count($productivity); ?> records</span>
                        <a href="manage_progress.php?project_id=<?php echo $task['project_id']; ?>" class="btn btn-sm btn-success ml-2">
                            <i class="fas fa-plus"></i> Add Record
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($productivity) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>User</th>
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
                                            <td><?php echo htmlspecialchars($record['subject']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($record['start_time'])) . ' - ' . date('g:i A', strtotime($record['end_time'])); ?></td>
                                            <td><?php echo number_format($record['time_rendered'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No productivity records for this task.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>