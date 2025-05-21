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

    // Check if user exists before deletion
    $check = $conn->prepare("SELECT * FROM users WHERE users_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // First, reassign any projects this user manages to the admin (or another manager)
        // You might want to get the first available admin instead of hardcoding
        $get_admin = $conn->prepare("SELECT users_id FROM users WHERE type = 1 AND users_id != ? LIMIT 1");
        $get_admin->bind_param("i", $user_id);
        $get_admin->execute();
        $admin_result = $get_admin->get_result();
        
        if ($admin_result->num_rows > 0) {
            $admin_row = $admin_result->fetch_assoc();
            $new_manager_id = $admin_row['users_id'];
            
            // Reassign projects
            $reassign = $conn->prepare("UPDATE project_list SET manager_id = ? WHERE manager_id = ?");
            $reassign->bind_param("ii", $new_manager_id, $user_id);
            $reassign->execute();
            $reassign->close();
            
            // Also remove user from project_users table
            $remove_from_projects = $conn->prepare("DELETE FROM project_users WHERE user_id = ?");
            $remove_from_projects->bind_param("i", $user_id);
            $remove_from_projects->execute();
            $remove_from_projects->close();
            
            // Now delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE users_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "User deleted successfully. Any managed projects were reassigned.";
            } else {
                $_SESSION['message'] = "Error deleting user after reassignment.";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Cannot delete user - no available admin to reassign projects to.";
        }
        
        $get_admin->close();
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