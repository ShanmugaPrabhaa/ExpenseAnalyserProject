<?php
// delete_notification.php
require_once 'conn.php';

// Validate and sanitize input
if (isset($_POST['reminder_id'])) {
    $reminder_id = $_POST['reminder_id'];

    // Perform deletion in the database
    $queryDelete = "DELETE FROM billreminders WHERE reminder_id = ?";
    $stmtDelete = $conn->prepare($queryDelete);
    $stmtDelete->bind_param("i", $reminder_id);
    
    if ($stmtDelete->execute()) {
        // Deletion successful
        header("Location: userdashboard.php"); // Redirect to notifications page or refresh
        exit();
    } else {
        // Error handling
        echo "Error: " . $stmtDelete->error;
    }
} else {
    // Handle case where reminder_id is not provided
    echo "Invalid request.";
}
?>
