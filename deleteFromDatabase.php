<?php
session_start();
require_once 'init.php';

// Validate session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    http_response_code(401); // Unauthorized
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['title'])) {
        $title = mysqli_real_escape_string($conn, $input['title']);
        $creatorId = $_SESSION['user_id'];

        $sql = "DELETE FROM NotificationTemplates WHERE template_creator_id = '$creatorId' AND noti_name = '$title'";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(array('message' => 'Data successfully deleted from database.'));
        } else {
            echo json_encode(array('message' => 'Database error: ' . mysqli_error($conn)));
        }
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(array('message' => 'Invalid form data.'));
    }
} else {
    http_response_code(405); // Method Not Allowed
}
?>
