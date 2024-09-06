<?php
require_once 'config.php';
require_once 'conn.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * 1; // Adjust as per your pagination logic

$query = "SELECT * FROM adminbuffer LIMIT 1 OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $offset);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);
?>
