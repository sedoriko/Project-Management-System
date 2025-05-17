<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate form token to prevent duplicate submissions
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Get user's projects for dropdown
$projects = [];
$project_query = "SELECT p.project_id, p.name 
                  FROM project_list p 
                  JOIN project_users pu ON p.project_id = pu.project_id 
                  WHERE pu.user_id = $user_id 
                  ORDER BY p.name";
$project_result = $conn->query($project_query);
while ($row = $project_result->fetch_assoc()) {
    $projects[] = $row;
}

// Get selected project ID from GET or POST
$selected_project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 
                     (isset($_POST['project_id']) ? intval($_POST['project_id']) : null);

// Get user's tasks for selected project
$tasks = [];
if ($selected_project_id) {
    $task_query = "SELECT task_id, task_name FROM task_list 
                   WHERE project_id = $selected_project_id 
                   ORDER BY task_name";
    $task_result = $conn->query($task_query);
    while ($row = $task_result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

// Handle productivity submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify form token
    if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
        $error = 'Invalid form submission';
    } else {
        $project_id = intval($_POST['project_id']);
        $task_id = !empty($_POST['task_id']) ? intval($_POST['task_id']) : null;
        $subject = trim($_POST['subject']);
        $comment = trim($_POST['comment']);
        $date = trim($_POST['date']);
        $start_time = trim($_POST['start_time']);
        $end_time = trim($_POST['end_time']);
        
        // Calculate time rendered
        $start = strtotime($start_time);
        $end = strtotime($end_time);
        $time_rendered = ($end - $start) / 3600; // Convert to hours
        
        // Validation
        if (empty($subject) || empty($date) || empty($start_time) || empty($end_time)) {
            $error = 'Required fields are missing';
        } elseif ($end <= $start) {
            $error = 'End time must be after start time';
        } else {
            // Insert productivity record
            $stmt = $conn->prepare("INSERT INTO user_productivity 
                                   (project_id, task_id, comment, subject, date, start_time, end_time, user_id, time_rendered) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssssid", $project_id, $task_id, $comment, $subject, $date, $start_time, $end_time, $user_id, $time_rendered);
            
            if ($stmt->execute()) {
                // Regenerate token
                $_SESSION['form_token'] = bin2hex(random_bytes(32));
                
                // Redirect to prevent duplicate submissions
                header("Location: manage_progress.php?success=Productivity+record+added+successfully&project_id=".$project_id);
                exit();
            } else {
                $error = 'Error adding productivity record: ' . $conn->error;
            }
        }
    }
}

// Get user's productivity records
$productivity = [];
$prod_query = "SELECT up.*, p.name as project_name, t.task_name 
               FROM user_productivity up 
               JOIN project_list p ON up.project_id = p.project_id 
               LEFT JOIN task_list t ON up.task_id = t.task_id 
               WHERE up.user_id = $user_id 
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
        min-height: 100vh;
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
        background-color: rgba(255, 255, 255, 0.92); /* Slightly more opaque for cards */
        border-radius: 10px;
    }
    
    .table {
        background-color: rgba(255, 255, 255, 0.95); /* More opaque for tables */
    }
    
    .alert {
        opacity: 0.95;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Manage My Productivity</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Productivity Record</h6>
                </div>
                <div class="card-body">
                    <form method="POST" id="productivityForm">
                        <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label">Project</label>
                                <select class="form-select" id="project_id" name="project_id" required>
                                    <option value="">Select Project</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo $project['project_id']; ?>" 
                                            <?php echo ($selected_project_id == $project['project_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($project['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="task_id" class="form-label">Task (optional)</label>
                                <select class="form-select" id="task_id" name="task_id">
                                    <option value="">Select Task</option>
                                    <?php if ($selected_project_id): ?>
                                        <?php foreach ($tasks as $task): ?>
                                            <option value="<?php echo $task['task_id']; ?>" 
                                                <?php echo (isset($_POST['task_id']) && $_POST['task_id'] == $task['task_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($task['task_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comments</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3"><?php 
                                echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '';
                            ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" 
                                       value="<?php echo isset($_POST['start_time']) ? htmlspecialchars($_POST['start_time']) : ''; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" 
                                       value="<?php echo isset($_POST['end_time']) ? htmlspecialchars($_POST['end_time']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save Record</button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Productivity Records</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Project</th>
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

<script>
// Disable submit button after form submission to prevent double clicks
document.getElementById('productivityForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').disabled = true;
});

// Load tasks when project is selected using AJAX
document.getElementById('project_id').addEventListener('change', function() {
    const projectId = this.value;
    if (projectId) {
        // Update task dropdown via AJAX
        fetch(`get_tasks.php?project_id=${projectId}`)
            .then(response => response.json())
            .then(tasks => {
                const taskSelect = document.getElementById('task_id');
                taskSelect.innerHTML = '<option value="">Select Task</option>';
                
                tasks.forEach(task => {
                    const option = document.createElement('option');
                    option.value = task.task_id;
                    option.textContent = task.task_name;
                    taskSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching tasks:', error));
    } else {
        document.getElementById('task_id').innerHTML = '<option value="">Select Task</option>';
    }
});
</script>

<?php include 'footer.php'; ?>