<?php
session_start();
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal_id = $_POST['goal_id'] ?? null;
    $user_id = $_SESSION['user_id'];
    $goal_name = $_POST['goal_name'];
    $target_amount = $_POST['target_amount'];
    $current_amount = $_POST['current_amount'];
    $target_date = $_POST['target_date'];

    if ($goal_id) {
        // Update existing goal
        $sql = "UPDATE Goals SET goal_name = ?, target_amount = ?, current_amount = ?, target_date = ? WHERE goal_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddssi", $goal_name, $target_amount, $current_amount, $target_date, $goal_id, $user_id);
    } else {
        // Insert new goal
        $sql = "INSERT INTO Goals (user_id, goal_name, target_amount, current_amount, target_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdds", $user_id, $goal_name, $target_amount, $current_amount, $target_date);
    }

    if ($stmt->execute()) {
        header("Location: goals.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
