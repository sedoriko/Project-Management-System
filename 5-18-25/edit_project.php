<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['type'] != 1 && $_SESSION['type'] != 2)) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch project details
$project = null;
$managers = [];
$users = [];
$assigned_users = [];

if ($project_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM project_list WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();

    // Get all managers
    $manager_query = "SELECT users_id, firstname, lastname FROM users WHERE type = 2 ORDER BY firstname, lastname";
    $manager_result = $conn->query($manager_query);
    while ($row = $manager_result->fetch_assoc()) {
        $managers[] = $row;
    }

    // Get all regular users
    $user_query = "SELECT users_id, firstname, lastname FROM users WHERE type = 3 ORDER BY firstname, lastname";
    $user_result = $conn->query($user_query);
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row;
    }

    // Get currently assigned users
    $assigned_result = $conn->prepare("SELECT user_id FROM project_users WHERE project_id = ?");
    $assigned_result->bind_param("i", $project_id);
    $assigned_result->execute();
    $assigned_data = $assigned_result->get_result();
    while ($row = $assigned_data->fetch_assoc()) {
        $assigned_users[] = $row['user_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $manager_id = intval($_POST['manager_id']);
    $selected_users = isset($_POST['users']) ? $_POST['users'] : [];

    // Validation
    if (empty($name) || empty($status) || empty($start_date) || empty($end_date) || empty($manager_id)) {
        $error = 'Required fields are missing';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = 'End date must be after start date';
    } else {
        // Update project
        $stmt = $conn->prepare("UPDATE project_list SET name = ?, description = ?, status = ?, start_date = ?, end_date = ?, manager_id = ? WHERE project_id = ?");
        $stmt->bind_param("sssssii", $name, $description, $status, $start_date, $end_date, $manager_id, $project_id);
        
        if ($stmt->execute()) {
            // Update assigned users
            $conn->query("DELETE FROM project_users WHERE project_id = $project_id");
            
            // First, assign the manager to the project
            $stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $project_id, $manager_id);
            $stmt->execute();
            
            // Then assign the selected users to the project
            foreach ($selected_users as $user_id) {
                $user_id = intval($user_id);
                // Skip if the user is the same as the manager (to avoid duplicate)
                if ($user_id != $manager_id) {
                    $stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $project_id, $user_id);
                    $stmt->execute();
                }
            }
            
            $success = 'Project updated successfully';
        } else {
            $error = 'Error updating project: ' . $conn->error;
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
            <h1 class="h3 mb-4">Edit Project</h1>
            
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
                            <label for="name" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ($project ? htmlspecialchars($project['name']) : ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ($project ? htmlspecialchars($project['description']) : '');
                            ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ($project ? htmlspecialchars($project['start_date']) : ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ($project ? htmlspecialchars($project['end_date']) : ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Pending') || ($project && $project['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo (isset($_POST['status']) && $_POST['status'] == 'In Progress') || ($project && $project['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Completed') || ($project && $project['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="manager_id" class="form-label">Project Manager</label>
                            <select class="form-select" id="manager_id" name="manager_id" required>
                                <option value="">Select Manager</option>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?php echo $manager['users_id']; ?>"
                                        <?php echo (isset($_POST['manager_id']) && $_POST['manager_id'] == $manager['users_id']) || 
                                                ($project && $project['manager_id'] == $manager['users_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($manager['firstname'] . ' ' . $manager['lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Assign Team Members</label>
                            <div class="row">
                                <?php foreach ($users as $user): ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="users[]" 
                                                   value="<?php echo $user['users_id']; ?>" 
                                                   id="user_<?php echo $user['users_id']; ?>"
                                                   <?php echo in_array($user['users_id'], $assigned_users) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="user_<?php echo $user['users_id']; ?>">
                                                <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Project</button>
                        <a href="project_list.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>