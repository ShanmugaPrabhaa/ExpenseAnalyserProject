<?php
session_start();
require_once 'config.php'; // Database configuration file
require_once 'conn.php';   // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task'])) {
    $task = trim($_POST['task']);
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

    if (!empty($task) && !empty($user_id)) {
        $stmt = $conn->prepare("INSERT INTO ToDoList (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);
        if ($stmt->execute()) {
            $response = array('success' => true, 'todo_id' => $stmt->insert_id);
        } else {
            $response = array('success' => false);
        }
        $stmt->close();
    } else {
        $response = array('success' => false);
    }

    $conn->close();
    echo json_encode($response);
}
?>
