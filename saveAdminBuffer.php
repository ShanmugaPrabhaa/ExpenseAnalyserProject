<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['username']) && isset($data['fullname']) && isset($data['role']) && isset($data['password'])) {
        // Logic to save the entry in the buffer
        // Replace this with your actual logic
        $success = true; // Set this based on whether the save was successful

        if ($success) {
            echo json_encode(['message' => 'Admin data successfully saved in buffer.']);
        } else {
            echo json_encode(['message' => 'Failed to save admin data in buffer.']);
        }
    } else {
        echo json_encode(['message' => 'Invalid data.']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
