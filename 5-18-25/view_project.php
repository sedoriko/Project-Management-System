<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($project_id == 0) {
    header("Location: project_list.php");
    exit();
}

// Get project details
$project = null;
$stmt = $conn->prepare("SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) as manager_name 
                       FROM project_list p 
                       JOIN users u ON p.manager_id = u.users_id 
                       WHERE p.project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    header("Location: project_list.php");
    exit();
}

// Check if current user has access to this project
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['type'];

if ($user_type != 1 && $project['manager_id'] != $user_id) {
    // Check if user is assigned to project
    $stmt = $conn->prepare("SELECT 1 FROM project_users WHERE project_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $project_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: project_list.php");
        exit();
    }
}

// Get assigned users
$assigned_users = [];
$user_query = "SELECT u.users_id, u.firstname, u.lastname, u.email 
               FROM users u 
               JOIN project_users pu ON u.users_id = pu.user_id 
               WHERE pu.project_id = $project_id 
               ORDER BY u.firstname, u.lastname";
$user_result = $conn->query($user_query);
while ($row = $user_result->fetch_assoc()) {
    $assigned_users[] = $row;
}

// Get project tasks
$tasks = [];
$task_query = "SELECT * FROM task_list WHERE project_id = $project_id ORDER BY date_created DESC";
$task_result = $conn->query($task_query);
while ($row = $task_result->fetch_assoc()) {
    $tasks[] = $row;
}

// Get project productivity
$productivity = [];
$prod_query = "SELECT up.*, t.task_name, CONCAT(u.firstname, ' ', u.lastname) as user_name 
               FROM user_productivity up 
               LEFT JOIN task_list t ON up.task_id = t.task_id 
               JOIN users u ON up.user_id = u.users_id 
               WHERE up.project_id = $project_id 
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
                <h1 class="h3 mb-0 text-gray-800">Project Details</h1>
                <div>
                    <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                        <a href="edit_project.php?id=<?php echo $project_id; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Project
                        </a>
                    <?php endif; ?>
                    <a href="project_list.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Projects
                    </a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Project Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><?php echo htmlspecialchars($project['name']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                            
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-<?php 
                                    echo $project['status'] == 'Completed' ? 'success' : 
                                         ($project['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                ?>">
                                    <?php echo $project['status']; ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Manager:</strong>
                                <?php echo htmlspecialchars($project['manager_name']); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Start Date:</strong>
                                <?php echo date('M d, Y', strtotime($project['start_date'])); ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>End Date:</strong>
                                <?php echo date('M d, Y', strtotime($project['end_date'])); ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Days Remaining:</strong>
                                <?php 
                                    $today = new DateTime();
                                    $end_date = new DateTime($project['end_date']);
                                    $interval = $today->diff($end_date);
                                    echo $interval->format('%r%a days');
                                ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Created:</strong>
                                <?php echo date('M d, Y H:i', strtotime($project['date_created'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Team Members</h6>
                            <span class="badge bg-primary"><?php echo count($assigned_users); ?> members</span>
                        </div>
                        <div class="card-body">
                            <?php if (count($assigned_users) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($assigned_users as $user): ?>
                                        <a href="view_user.php?id=<?php echo $user['users_id']; ?>" 
                                           class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']); ?></h6>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>No team members assigned to this project.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Tasks</h6>
                            <div>
                                <span class="badge bg-primary"><?php echo count($tasks); ?> tasks</span>
                                <?php if ($_SESSION['type'] == 1 || $_SESSION['type'] == 2): ?>
                                    <a href="manage_task.php?project_id=<?php echo $project_id; ?>" class="btn btn-sm btn-success ml-2">
                                        <i class="fas fa-plus"></i> Add Task
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (count($tasks) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Task</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tasks as $task): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $task['status'] == 'Completed' ? 'success' : 
                                                                 ($task['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                                        ?>">
                                                            <?php echo $task['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="view_task.php?id=<?php echo $task['task_id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No tasks created for this project.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Productivity Records</h6>
                    <span class="badge bg-primary"><?php echo count($productivity); ?> records</span>
                </div>
                <div class="card-body">
                    <?php if (count($productivity) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>User</th>
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
                                            <td><?php echo $record['task_name'] ? htmlspecialchars($record['task_name']) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($record['subject']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($record['start_time'])) . ' - ' . date('g:i A', strtotime($record['end_time'])); ?></td>
                                            <td><?php echo number_format($record['time_rendered'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No productivity records for this project.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>