<?php
require_once 'db_connect.php';

// Only admin can access this page
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 1) {
    header("Location: login.php");
    exit();
}

// Handle search and sort
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
}

$sort_field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'date_created';
$sort_direction = isset($_GET['sort_direction']) && strtolower($_GET['sort_direction']) === 'asc' ? 'ASC' : 'DESC';

// Validate sort field to prevent SQL injection
$allowed_sort_fields = ['users_id', 'firstname', 'lastname', 'email', 'type', 'date_created'];
if (!in_array($sort_field, $allowed_sort_fields)) {
    $sort_field = 'date_created';
}

// Get all users with search and sort
$users = array();
$query = "SELECT users_id, firstname, lastname, email, type, date_created FROM users";

if (!empty($search_term)) {
    $query .= " WHERE firstname LIKE ? OR lastname LIKE ? OR email LIKE ?";
}

$query .= " ORDER BY $sort_field $sort_direction";

$stmt = $conn->prepare($query);

if (!empty($search_term)) {
    $search_param = "%{$search_term}%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();

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
    
    /* Sortable header links */
    .sortable-header {
        color: inherit;
        text-decoration: none;
    }
    .sortable-header:hover {
        color: #4e73df;
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
                                <a href="?search=<?php echo urlencode($search_term); ?>&sort_field=<?php echo $sort_field; ?>&sort_direction=<?php echo $sort_direction === 'ASC' ? 'desc' : 'asc'; ?>" 
                                class="btn btn-outline-secondary ml-2" title="Sort Direction">
                                    <i class="fas fa-sort-<?php echo $sort_direction === 'ASC' ? 'down' : 'up'; ?>"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search_term); ?>&sort_field=users_id&sort_direction=<?php echo ($sort_field == 'users_id' && $sort_direction == 'ASC') ? 'desc' : 'asc'; ?>" class="sortable-header">
                                            ID <?php echo $sort_field == 'users_id' ? ($sort_direction == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search_term); ?>&sort_field=firstname&sort_direction=<?php echo ($sort_field == 'firstname' && $sort_direction == 'ASC') ? 'desc' : 'asc'; ?>" class="sortable-header">
                                            Name <?php echo $sort_field == 'firstname' ? ($sort_direction == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search_term); ?>&sort_field=email&sort_direction=<?php echo ($sort_field == 'email' && $sort_direction == 'ASC') ? 'desc' : 'asc'; ?>" class="sortable-header">
                                            Email <?php echo $sort_field == 'email' ? ($sort_direction == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search_term); ?>&sort_field=type&sort_direction=<?php echo ($sort_field == 'type' && $sort_direction == 'ASC') ? 'desc' : 'asc'; ?>" class="sortable-header">
                                            Type <?php echo $sort_field == 'type' ? ($sort_direction == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?search=<?php echo urlencode($search_term); ?>&sort_field=date_created&sort_direction=<?php echo ($sort_field == 'date_created' && $sort_direction == 'ASC') ? 'desc' : 'asc'; ?>" class="sortable-header">
                                            Date Created <?php echo $sort_field == 'date_created' ? ($sort_direction == 'ASC' ? '↑' : '↓') : ''; ?>
                                        </a>
                                    </th>
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
                                <?php if (empty($users)): ?>
                                    <tr><td colspan="6" class="text-center">No users found.</td></tr>
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