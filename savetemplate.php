<?php
session_start();

// Check if the request is a POST request and handle data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Assuming you have a database connection established
    // Replace with your database connection details
    require_once init.php;

    // Retrieve data from POST request (assuming JSON format)
    $data = json_decode(file_get_contents("php://input"), true);

    // Extract user_id from session
    if (!isset($_SESSION['user_id'])) {
        die("User session not found.");
    }
    $user_id = $_SESSION['user_id'];

    // Prepare SQL statement to insert data into NotificationTemplates table
    $sql = "INSERT INTO NotificationTemplates (user_id, noti_name, noti_template) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $data['title'], $data['content']);

    // Execute SQL statement
    if ($stmt->execute()) {
        echo "Data inserted successfully into database.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close connections
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
