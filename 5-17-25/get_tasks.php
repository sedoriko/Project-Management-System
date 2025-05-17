<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);
    $tasks = [];
    
    $task_query = "SELECT task_id, task_name FROM task_list 
                   WHERE project_id = $project_id 
                   ORDER BY task_name";
    $task_result = $conn->query($task_query);
    
    while ($row = $task_result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    echo json_encode($tasks);
} else {
    echo json_encode([]);
}
?>