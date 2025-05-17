<?php
require_once 'db_connect.php';

// Only admin can access this page
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 1) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user details
$user = null;
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $type = intval($_POST['type']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($firstname) || empty($lastname) || empty($email)) {
        $error = 'Required fields are missing';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists for another user
        $stmt = $conn->prepare("SELECT users_id FROM users WHERE email = ? AND users_id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already exists for another user';
        } else {
            // Update user
            if (!empty($password)) {
                // Update with password change
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, password = ?, type = ? WHERE users_id = ?");
                $stmt->bind_param("ssssii", $firstname, $lastname, $email, $hashed_password, $type, $user_id);
            } else {
                // Update without password change
                $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, type = ? WHERE users_id = ?");
                $stmt->bind_param("sssii", $firstname, $lastname, $email, $type, $user_id);
            }
            
            if ($stmt->execute()) {
                $success = 'User updated successfully';
            } else {
                $error = 'Error updating user: ' . $conn->error;
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
            <h1 class="h3 mb-4">Edit User</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" 
                                       value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ($user ? htmlspecialchars($user['firstname']) : ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" 
                                       value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ($user ? htmlspecialchars($user['lastname']) : ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ($user ? htmlspecialchars($user['email']) : ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">User Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="1" <?php echo (isset($_POST['type']) && $_POST['type'] == 1) || ($user && $user['type'] == 1) ? 'selected' : ''; ?>>Admin</option>
                                <option value="2" <?php echo (isset($_POST['type']) && $_POST['type'] == 2) || ($user && $user['type'] == 2) ? 'selected' : ''; ?>>Manager</option>
                                <option value="3" <?php echo (isset($_POST['type']) && $_POST['type'] == 3) || ($user && $user['type'] == 3) ? 'selected' : ''; ?>>Employee</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update User</button>
                        <a href="user_list.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>