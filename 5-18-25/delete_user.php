<?php
require_once 'db_connect.php';
session_start();

// Only allow admin to delete users
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 1) {
    header("Location: login.php");
    exit();
}

// Validate and sanitize user ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int) $_GET['id'];

    // Prevent admin from deleting their own account
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['message'] = "You cannot delete your own account.";
        header("Location: user_list.php");
        exit();
    }

    // Optional: check if user exists before deletion
    $check = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE users_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "User deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting user.";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "User not found.";
    }

    $check->close();
} else {
    $_SESSION['message'] = "Invalid user ID.";
}

header("Location: user_list.php");
exit();
?>
