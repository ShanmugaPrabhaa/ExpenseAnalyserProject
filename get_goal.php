<?php
session_start();
require_once 'init.php';

if (isset($_GET['id'])) {
    $goal_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM Goals WHERE goal_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $goal_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $goal = $result->fetch_assoc();

    echo json_encode($goal);
}
?>
