<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$user_id = $_SESSION['user_id'];
$expense_id = $_GET['id'];

require_once 'init.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT * FROM Expenses WHERE expense_id = :expense_id AND user_id = :user_id");
    $stmt->execute(['expense_id' => $expense_id, 'user_id' => $user_id]);
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($expense);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
}
?>
