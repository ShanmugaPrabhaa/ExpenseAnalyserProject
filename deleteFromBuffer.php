<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the request contains a title
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['title'])) {
        $title = $data['title'];

        // Logic to delete the entry from the buffer
        // Replace this with your actual logic
        $success = true; // Set this based on whether the deletion was successful

        if ($success) {
            echo json_encode(['message' => 'Data successfully deleted from buffer.']);
        } else {
            echo json_encode(['message' => 'Failed to delete data from buffer.']);
        }
    } else {
        echo json_encode(['message' => 'Invalid data. Title not provided.']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
