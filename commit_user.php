<?php
session_start();
require_once 'init.php';

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    header("Location: login.php");
    exit();
}

// Check if the user has permission to manage users
require_once 'check_permission.php';
if (!check_permission($conn, 'add_admins')) {
    header("Location: access_denied.html");
    exit();
}

// Get the user details from POST request
$admin_id = $_POST['admin_id'];
$username = $_POST['username'];
$email = $_POST['email'];
$fullname = $_POST['fullname'];
$role_id = $_POST['role_id'];

// Fetch the password hash from adminbuffer (assuming you have a way to fetch or generate it)
$password_hash = ''; // Replace with your logic to fetch or generate password hash

// Insert the user into Users table
$sql_insert = "INSERT INTO Users (username, password_hash, email, fullname, role_id) 
               VALUES (?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ssssi", $username, $password_hash, $email, $fullname, $role_id);
$stmt_insert->execute();
$stmt_insert->close();

// Redirect back to the admin management page
header("Location: view_adminbuffer.php");
exit();
?>
