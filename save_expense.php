<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
require_once 'config.php';
require_once 'conn.php';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $expense_id = $_POST['expense_id'] ?? null;
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    if ($expense_id) {
        // Update existing expense
        $stmt = $pdo->prepare("UPDATE Expenses SET category_id = :category_id, amount = :amount, description = :description, date = :date WHERE expense_id = :expense_id AND user_id = :user_id");
        $stmt->execute(['category_id' => $category_id, 'amount' => $amount, 'description' => $description, 'date' => $date, 'expense_id' => $expense_id, 'user_id' => $user_id]);
    } else {
        // Create new expense
        $stmt = $pdo->prepare("INSERT INTO Expenses (user_id, category_id, amount, description, date) VALUES (:user_id, :category_id, :amount, :description, :date)");
        $stmt->execute(['user_id' => $user_id, 'category_id' => $category_id, 'amount' => $amount, 'description' => $description, 'date' => $date]);
    }

    echo 'success';
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
}
?>
