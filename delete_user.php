<?php
session_start();
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the user is logged in and has the right permissions
    require_once 'check_permission.php';
    if (!check_permission($conn, 'add_admins')) {
        header("Location: access_denied.html");
        exit();
    }

    // Get the user ID to be deleted from the POST request
    $user_id = $_POST['user_id'];
    $deleted_by_admin_id = $_SESSION['user_id'];

    // Fetch the username before deleting from Users table
    $stmt_fetch_username = $conn->prepare("
        SELECT username FROM Users WHERE user_id = ?
    ");
    $stmt_fetch_username->bind_param("i", $user_id);
    $stmt_fetch_username->execute();
    $stmt_fetch_username->bind_result($username);
    $stmt_fetch_username->fetch();
    $stmt_fetch_username->close();

    if ($username) {
        // Delete related records in userpermissions table
        $stmt_delete_permissions = $conn->prepare("
            DELETE FROM userpermissions 
            WHERE user_id = ?
        ");
        $stmt_delete_permissions->bind_param("i", $user_id);

        if ($stmt_delete_permissions->execute()) {
            // Proceed to delete the user from the Users table
            $stmt_delete_user = $conn->prepare("
                DELETE FROM Users 
                WHERE user_id = ?
            ");
            $stmt_delete_user->bind_param("i", $user_id);

            if ($stmt_delete_user->execute()) {
                // Delete the user from the adminbuffer table
                $stmt_delete_adminbuffer = $conn->prepare("
                    DELETE FROM adminbuffer 
                    WHERE username = ?
                ");
                $stmt_delete_adminbuffer->bind_param("s", $username);
                $stmt_delete_adminbuffer->execute();
                $stmt_delete_adminbuffer->close();

                // Redirect back to the manage users page with a success message
                header("Location: view_admin.php");
            } else {
                // Handle error during user deletion
                echo "Error deleting user: " . $conn->error;
            }

            $stmt_delete_user->close();
        } else {
            // Handle error during permissions deletion
            echo "Error deleting permissions: " . $conn->error;
        }

        $stmt_delete_permissions->close();
    } else {
        echo "User not found.";
    }

    $conn->close();
} else {
    // Redirect back if the request method is not POST
    header("Location: manage_users.php");
}
?>
