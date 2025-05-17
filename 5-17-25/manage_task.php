<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['type'] != 1 && $_SESSION['type'] != 2)) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['type'];
$error = '';
$success = '';
$task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch task details
$task = null;
if ($task_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM task_list WHERE task_id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
}

// Get projects for dropdown - only projects assigned to this admin/manager
$projects = [];
if ($user_type == 1) { // Admin can see all projects
    $project_query = "SELECT project_id, name FROM project_list ORDER BY name";
} else { // Manager can only see projects they're assigned to
    $project_query = "SELECT p.project_id, p.name 
                     FROM project_list p
                     JOIN project_users pu ON p.project_id = pu.project_id
                     WHERE pu.user_id = $user_id
                     ORDER BY p.name";
}

$project_result = $conn->query($project_query);
while ($row = $project_result->fetch_assoc()) {
    $projects[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = intval($_POST['project_id']);
    $task_name = trim($_POST['task_name']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);
    
    // Validation
    if (empty($project_id) || empty($task_name) || empty($status)) {
        $error = 'Required fields are missing';
    } else {
        // Additional validation - check if user has access to the selected project
        $valid_project = false;
        foreach ($projects as $project) {
            if ($project['project_id'] == $project_id) {
                $valid_project = true;
                break;
            }
        }
        
        if (!$valid_project) {
            $error = 'You do not have permission to create tasks for this project';
        } else {
            if ($task_id > 0) {
                // Update task
                $stmt = $conn->prepare("UPDATE task_list SET project_id = ?, task_name = ?, description = ?, status = ? WHERE task_id = ?");
                $stmt->bind_param("isssi", $project_id, $task_name, $description, $status, $task_id);
            } else {
                // Create new task
                $stmt = $conn->prepare("INSERT INTO task_list (project_id, task_name, description, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $project_id, $task_name, $description, $status);
            }
            
            if ($stmt->execute()) {
                $success = $task_id > 0 ? 'Task updated successfully' : 'Task created successfully';
                if ($task_id == 0) {
                    // Clear form if it was a new task
                    $_POST = array();
                    $task_id = $stmt->insert_id;
                    // Refresh task data
                    $stmt = $conn->prepare("SELECT * FROM task_list WHERE task_id = ?");
                    $stmt->bind_param("i", $task_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $task = $result->fetch_assoc();
                }
            } else {
                $error = 'Error saving task: ' . $conn->error;
            }
        }
    }
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
            <h1 class="h3 mb-4"><?php echo $task_id ? 'Edit' : 'Create'; ?> Task</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="project_id" class="form-label">Project</label>
                            <select class="form-select" id="project_id" name="project_id" required>
                                <option value="">Select Project</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['project_id']; ?>" 
                                        <?php echo (isset($_POST['project_id']) && $_POST['project_id'] == $project['project_id']) || 
                                               ($task && $task['project_id'] == $project['project_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($project['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="task_name" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="task_name" name="task_name" 
                                   value="<?php echo isset($_POST['task_name']) ? htmlspecialchars($_POST['task_name']) : 
                                          ($task ? htmlspecialchars($task['task_name']) : ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : 
                                      ($task ? htmlspecialchars($task['description']) : '');
                            ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Pending') || 
                                                         ($task && $task['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo (isset($_POST['status']) && $_POST['status'] == 'In Progress') || 
                                                           ($task && $task['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Completed') || 
                                                        ($task && $task['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Task</button>
                        <a href="task_list.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>