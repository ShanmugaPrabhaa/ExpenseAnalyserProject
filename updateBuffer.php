<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the request contains the necessary data
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['title']) && isset($data['content']) && isset($data['originalTitle'])) {
        $title = $data['title'];
        $content = $data['content'];
        $originalTitle = $data['originalTitle'];

        // Logic to update the entry in the buffer
        // Replace this with your actual logic
        $success = true; // Set this based on whether the update was successful

        if ($success) {
            echo json_encode(['message' => 'Data successfully updated in buffer.']);
        } else {
            echo json_encode(['message' => 'Failed to update data in buffer.']);
        }
    } else {
        echo json_encode(['message' => 'Invalid data.']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
