<?php
session_start();
require_once 'config.php'; // Database configuration file
require_once 'conn.php';   // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['todo_id'])) {
    $todo_id = intval($_POST['todo_id']);
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

    if (!empty($todo_id) && !empty($user_id)) {
        $stmt = $conn->prepare("DELETE FROM ToDoList WHERE todo_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $todo_id, $user_id);
        if ($stmt->execute()) {
            $response = array('success' => true);
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
