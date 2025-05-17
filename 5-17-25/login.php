<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['users_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['lastname'] = $user['lastname'];
            $_SESSION['type'] = $user['type'];
            
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Infinity Corporation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-image: url('image/login_bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 380px;
            width: 90%;
            margin: 10px auto;
            background: linear-gradient(145deg, rgba(255,255,255,0.85) 0%, rgba(138,43,226,0.15) 100%);
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(138,43,226,0.3) 100%);
            color: #333;
            padding: 5px 5px 5px;
            text-align: center;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .logo {
            height: 40px;
        }
        
        .welcome-text {
            font-size: 1.2rem;
            color:rgb(103, 8, 170);
            margin: 0;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .login-form {
            padding: 15px 20px 20px;
            background: linear-gradient(145deg, rgba(255,255,255,0.9) 0%, rgba(52,152,219,0.1) 100%);
        }
        
        .form-control {
            padding: 8px 12px;
            height: 40px;
            font-size: 0.9rem;
            border-radius: 6px;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: rgba(255,255,255,0.8);
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7a7a7a;
            z-index: 5;
            background: none;
            border: none;
            padding: 3px;
        }
        
        .btn-primary {
            background-color:rgb(34, 129, 192);
            border-color: #3498db;
            padding: 8px;
            height: 40px;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .alert {
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .form-label {
            margin-bottom: 5px;
            font-size: 1rem; /* Increased from 0.9rem to 1rem */
            font-weight: 400; /* Added medium font weight */
        }
        
        @media (max-width: 576px) {
            .login-container {
                width: 95%;
                margin: 5px auto;
            }
            .login-header {
                padding: 12px 15px 8px;
            }
            .logo {
                height: 40px;
            }
            .welcome-text {
                font-size: 1.2rem;
            }
            .login-form {
                padding: 12px 15px 15px;
            }
            .form-label {
                font-size: 0.95rem; /* Slightly smaller on mobile but still larger than before */
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="image/logo1.png" alt="Infinity Corporation Logo" class="logo">
            <p class="welcome-text">Login To Your Account</p>
        </div>
        <div class="login-form">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-2">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-1">Login</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
        
        // Focus email field on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>