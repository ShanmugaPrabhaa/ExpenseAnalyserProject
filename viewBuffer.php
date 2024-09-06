<?php
session_start();

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, send an unauthorized response
    http_response_code(401); // Unauthorized
    exit();
}

// Respond with the buffered data
if (isset($_SESSION['bufferData'])) {
    echo json_encode($_SESSION['bufferData']);
} else {
    echo json_encode(array());
}
?>
