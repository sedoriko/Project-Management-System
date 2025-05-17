<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$view_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($view_user_id == 0) {
    header("Location: user_list.php");
    exit();
}

// Only admin can view other users, others can only view their own profile
if ($_SESSION['type'] != 1 && $_SESSION['user_id'] != $view_user_id) {
    header("Location: home.php");
    exit();
}

// Get user details
$user = null;
$stmt = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
$stmt->bind_param("i", $view_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: user_list.php");
    exit();
}

// Get user's projects
$projects = [];
$project_query = "SELECT p.* FROM project_list p 
                  JOIN project_users pu ON p.project_id = pu.project_id 
                  WHERE pu.user_id = $view_user_id 
                  ORDER BY p.date_created DESC";
$project_result = $conn->query($project_query);
while ($row = $project_result->fetch_assoc()) {
    $projects[] = $row;
}

// Get user's productivity
$productivity = [];
$prod_query = "SELECT up.*, p.name as project_name, t.task_name 
               FROM user_productivity up 
               JOIN project_list p ON up.project_id = p.project_id 
               LEFT JOIN task_list t ON up.task_id = t.task_id 
               WHERE up.user_id = $view_user_id 
               ORDER BY up.date DESC, up.start_time DESC 
               LIMIT 10";
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
        background-color: rgba(255, 255, 255, 0.88); /* 88% opacity white background */
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }
    
    .card {
        background-color: rgba(255, 255, 255, 0.92); /* Slightly more opaque for cards */
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    
    .table {
        background-color: rgba(255, 255, 255, 0.97); /* More opaque for tables */
    }
    
    /* Badge styling */
    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }
    
    /* Hover effects */
    .list-group-item:hover {
        background-color: rgba(248, 249, 250, 0.95);
    }
    
    /* Action buttons */
    .btn-sm {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">User Profile</h1>
                <div>
                    <?php if ($_SESSION['type'] == 1): ?>
                        <a href="edit_user.php?id=<?php echo $view_user_id; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $_SESSION['type'] == 1 ? 'user_list.php' : 'home.php'; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Name:</strong>
                                <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                            </div>

                            <div class="mb-3">
                                <strong>ID:</strong>
                                <?php echo htmlspecialchars($user['users_id']); ?>
                            </div>

                            <div class="mb-3">
                                <strong>Email:</strong>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>User Type:</strong>
                                <span class="badge bg-<?php 
                                    echo $user['type'] == 1 ? 'danger' : 
                                         ($user['type'] == 2 ? 'primary' : 'secondary'); 
                                ?>">
                                    <?php 
                                        echo $user['type'] == 1 ? 'Admin' : 
                                             ($user['type'] == 2 ? 'Manager' : 'Employee'); 
                                    ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Member Since:</strong>
                                <?php echo date('M d, Y', strtotime($user['date_created'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Assigned Projects</h6>
                            <span class="badge bg-primary"><?php echo count($projects); ?> projects</span>
                        </div>
                        <div class="card-body">
                            <?php if (count($projects) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($projects as $project): ?>
                                        <a href="view_project.php?id=<?php echo $project['project_id']; ?>" 
                                           class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($project['name']); ?></h6>
                                                <small class="text-<?php 
                                                    echo $project['status'] == 'Completed' ? 'success' : 
                                                         ($project['status'] == 'In Progress' ? 'primary' : 'warning'); 
                                                ?>">
                                                    <?php echo $project['status']; ?>
                                                </small>
                                            </div>
                                            <small>Start: <?php echo date('M d, Y', strtotime($project['start_date'])); ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>No projects assigned.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Productivity</h6>
                            <span class="badge bg-primary"><?php echo count($productivity); ?> records</span>
                        </div>
                        <div class="card-body">
                            <?php if (count($productivity) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Project</th>
                                                <th>Task</th>
                                                <th>Hours</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($productivity as $record): ?>
                                                <tr>
                                                    <td><?php echo date('M d', strtotime($record['date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($record['project_name']); ?></td>
                                                    <td><?php echo $record['task_name'] ? htmlspecialchars($record['task_name']) : 'N/A'; ?></td>
                                                    <td><?php echo number_format($record['time_rendered'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="reports.php?user_id=<?php echo $view_user_id; ?>" class="btn btn-sm btn-primary">View All</a>
                            <?php else: ?>
                                <p>No productivity records found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>