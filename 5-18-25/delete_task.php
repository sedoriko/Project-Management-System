<?php
require_once 'db_connect.php';

// Authentication check
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only admin or manager can delete tasks
if ($_SESSION['type'] != 1 && $_SESSION['type'] != 2) {
    $_SESSION['error'] = "You don't have permission to delete tasks";
    header("Location: task_list.php");
    exit();
}

if (isset($_GET['id'])) {
    $task_id = intval($_GET['id']); // Sanitize input
    
    // For managers, verify they are the manager of the project containing this task
    if ($_SESSION['type'] == 2) {
        $check_query = "SELECT p.manager_id 
                       FROM task_list t
                       JOIN project_list p ON t.project_id = p.project_id
                       WHERE t.task_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['manager_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = "You can only delete tasks from projects you manage";
                header("Location: task_list.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Task not found";
            header("Location: task_list.php");
            exit();
        }
        $stmt->close();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // 1. Delete from user_productivity (has FK to task_list)
        $delete_productivity = $conn->prepare("DELETE FROM user_productivity WHERE task_id = ?");
        $delete_productivity->bind_param("i", $task_id);
        if (!$delete_productivity->execute()) {
            throw new Exception("Failed to delete productivity records: " . $conn->error);
        }
        $delete_productivity->close();
        
        // 2. Finally delete the task itself
        $delete_task = $conn->prepare("DELETE FROM task_list WHERE task_id = ?");
        $delete_task->bind_param("i", $task_id);
        if (!$delete_task->execute()) {
            throw new Exception("Failed to delete task: " . $conn->error);
        }
        
        // Check if any rows were affected
        if ($delete_task->affected_rows === 0) {
            throw new Exception("No task found with that ID");
        }
        $delete_task->close();
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Commit transaction
        $conn->commit();
        $_SESSION['message'] = "Task deleted successfully";
        
    } catch (Exception $e) {
        // Rollback transaction if something fails
        $conn->rollback();
        // Re-enable foreign key checks even if operation fails
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $_SESSION['error'] = "Error deleting task: " . $e->getMessage();
        error_log("Task deletion error: " . $e->getMessage());
    }
}

header("Location: task_list.php");
exit();
?>