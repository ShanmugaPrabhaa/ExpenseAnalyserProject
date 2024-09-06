<?php
session_start();
require_once 'init.php';

if (isset($_GET['id'])) {
    $goal_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $sql = "DELETE FROM Goals WHERE goal_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $goal_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
