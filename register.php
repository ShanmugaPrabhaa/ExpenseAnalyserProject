<?php
require_once 'config.php';
require_once 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        header("Location: register.html?error=email_exists");
        exit();
    }



    // Insert user into database
    $role_id = 1; // Default role for new users
    $insert_stmt = $conn->prepare("INSERT INTO Users (username, password_hash, email, fullname, role_id) VALUES (?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssssi", $username, $password, $email, $fullname, $role_id);

    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        $conn->close();
        header("Location: login.php");
        exit();
    } else {
        $insert_stmt->close();
        $conn->close();
        header("Location: register.html?error=insert_failed");
        exit();
    }
} else {
    header("Location: register.html");
    exit();
}
?>
