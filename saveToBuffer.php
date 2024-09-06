<?php
session_start();

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, send an unauthorized response
    http_response_code(401); // Unauthorized
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receive and process form data
    $formData = json_decode(file_get_contents('php://input'), true); // Assuming data is sent as JSON

    // Initialize bufferData array if it doesn't exist
    if (!isset($_SESSION['bufferData'])) {
        $_SESSION['bufferData'] = [];
    }

    // Store the received form data in the bufferData session variable
    $_SESSION['bufferData'][] = $formData;

    // Respond with a success message
    echo json_encode(array('message' => 'Form data received and buffered.'));
} else {
    http_response_code(405); // Method Not Allowed
}
?>
