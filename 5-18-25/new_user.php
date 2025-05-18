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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $type = intval($_POST['type']);
    
    // Validate inputs
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT users_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $firstname, $lastname, $email, $hashed_password, $type);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'User created successfully.';
                header('Location: user_list.php');
                exit();
            } else {
                $error = 'Error creating user: ' . $conn->error;
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
            <h1 class="h3 mb-4">Create New User</h1>
            
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
                                       value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" 
                                       value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">User Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="1" <?php echo (isset($_POST['type']) && $_POST['type'] == 1) ? 'selected' : ''; ?>>Admin</option>
                                <option value="2" <?php echo (isset($_POST['type']) && $_POST['type'] == 2) ? 'selected' : ''; ?>>Manager</option>
                                <option value="3" <?php echo (isset($_POST['type']) && $_POST['type'] == 3) ? 'selected' : ''; ?>>Employee</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="user_list.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>