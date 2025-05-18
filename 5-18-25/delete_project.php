<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only admin or manager can delete projects
if ($_SESSION['type'] != 1 && $_SESSION['type'] != 2) {
    $_SESSION['error'] = "You don't have permission to delete projects";
    header("Location: project_list.php");
    exit();
}

if (isset($_GET['id'])) {
    $project_id = intval($_GET['id']); // Sanitize input
    
    // For managers, verify they are the manager of this project
    if ($_SESSION['type'] == 2) {
        $check_query = "SELECT manager_id FROM project_list WHERE project_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['manager_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = "You can only delete projects you manage";
                header("Location: project_list.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Project not found";
            header("Location: project_list.php");
            exit();
        }
        $stmt->close();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // 1. Delete from user_productivity (has FK to project_list)
        $delete_productivity = $conn->prepare("DELETE FROM user_productivity WHERE project_id = ?");
        $delete_productivity->bind_param("i", $project_id);
        if (!$delete_productivity->execute()) {
            throw new Exception("Failed to delete productivity records: " . $conn->error);
        }
        $delete_productivity->close();
        
        // 2. Delete from task_list (has FK to project_list)
        $delete_tasks = $conn->prepare("DELETE FROM task_list WHERE project_id = ?");
        $delete_tasks->bind_param("i", $project_id);
        if (!$delete_tasks->execute()) {
            throw new Exception("Failed to delete tasks: " . $conn->error);
        }
        $delete_tasks->close();
        
        // 3. Delete from project_users (has FK to project_list)
        $delete_project_users = $conn->prepare("DELETE FROM project_users WHERE project_id = ?");
        $delete_project_users->bind_param("i", $project_id);
        if (!$delete_project_users->execute()) {
            throw new Exception("Failed to delete project users: " . $conn->error);
        }
        $delete_project_users->close();
        
        // 4. Finally delete the project itself
        $delete_project = $conn->prepare("DELETE FROM project_list WHERE project_id = ?");
        $delete_project->bind_param("i", $project_id);
        if (!$delete_project->execute()) {
            throw new Exception("Failed to delete project: " . $conn->error);
        }
        
        // Check if any rows were affected
        if ($delete_project->affected_rows === 0) {
            throw new Exception("No project found with that ID");
        }
        $delete_project->close();
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Commit transaction
        $conn->commit();
        $_SESSION['message'] = "Project deleted successfully";
        
    } catch (Exception $e) {
        // Rollback transaction if something fails
        $conn->rollback();
        // Re-enable foreign key checks even if operation fails
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $_SESSION['error'] = "Error deleting project: " . $e->getMessage();
        error_log("Project deletion error: " . $e->getMessage());
    }
}

header("Location: project_list.php");
exit();
?>