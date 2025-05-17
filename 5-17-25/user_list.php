<?php
require_once 'db_connect.php';

// Only admin can access this page
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 1) {
    header("Location: login.php");
    exit();
}

// Get all users
$users = array();
$query = "SELECT users_id, firstname, lastname, email, type, date_created FROM users ORDER BY date_created DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
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
    
    /* Table header styling */
    .table thead th {
        background-color: rgba(248, 249, 250, 0.98);
        border-bottom: 2px solid #dee2e6;
    }
    
    /* Hover effects for better UX */
    .table tbody tr:hover {
        background-color: rgba(248, 249, 250, 0.95);
    }
    
    /* Button styling */
    .btn-sm {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Action buttons spacing */
    .btn-sm + .btn-sm {
        margin-left: 5px;
    }
</style>


<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">User Management</h1>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Users List</h6>
                    <a href="new_user.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['users_id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php 
                                                switch ($user['type']) {
                                                    case 1: echo 'Admin'; break;
                                                    case 2: echo 'Manager'; break;
                                                    case 3: echo 'Employee'; break;
                                                    default: echo 'Unknown';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['date_created'])); ?></td>
                                        <td>
                                            <a href="view_user.php?id=<?php echo $user['users_id']; ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_user.php?id=<?php echo $user['users_id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_user.php?id=<?php echo $user['users_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
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