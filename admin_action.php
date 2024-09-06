<?php
require_once 'config.php';
require_once 'conn.php';

$action = $_POST['action'];

switch ($action) {
    case 'save':
        $username = $_POST['userName'];
        $fullname = $_POST['fullName'];
        $email = $_POST['email'];
        $role_id = $_POST['roleName'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $query = "INSERT INTO adminbuffer (username, password_hash, email, fullname, role_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $username, $password, $email, $fullname, $role_id);
        if ($stmt->execute()) {
            $newPage = $conn->insert_id; // Assuming new page number is the new record's ID
            echo json_encode(['success' => true, 'newPage' => $newPage]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'update':
        $admin_id = $_POST['adminId'];
        $username = $_POST['userName'];
        $fullname = $_POST['fullName'];
        $email = $_POST['email'];
        $role_id = $_POST['roleName'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $query = "UPDATE adminbuffer SET username = ?, password_hash = ?, email = ?, fullname = ?, role_id = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssii", $username, $password, $email, $fullname, $role_id, $admin_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'drop':
        $admin_id = $_POST['adminId'];

        $query = "DELETE FROM adminbuffer WHERE admin_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $admin_id);
        if ($stmt->execute()) {
            $newPage = $admin_id - 1; // Move to the previous page
            echo json_encode(['success' => true, 'newPage' => $newPage]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
}
?>
