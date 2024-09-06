<?php
function check_permission($conn, $required_permission) {
    // Start the session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Fetch the user_id and role_id from the session
    $user_id = $_SESSION['user_id'];
    $role_id = $_SESSION['role_id'];

    // Get the permission_id for the required permission
    $stmt = $conn->prepare("SELECT permission_id FROM Permissions WHERE permission_name = ?");
    $stmt->bind_param("s", $required_permission);
    $stmt->execute();
    $stmt->bind_result($permission_id);
    $stmt->fetch();
    $stmt->close();

    if (!$permission_id) {
        // If the required permission does not exist, deny access
        return false;
    }

    // Check if the user has an overriding permission in UserPermissions
    $stmt = $conn->prepare("
        SELECT is_granted FROM UserPermissions
        WHERE user_id = ? AND permission_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $permission_id);
    $stmt->execute();
    $stmt->bind_result($is_granted);

    if ($stmt->fetch()) {
        $stmt->close();
        return (bool) $is_granted;
    }
    $stmt->close();

    // If no user-specific permission is found, check the role-based permission
    $stmt = $conn->prepare("
        SELECT 1 FROM RolePermissions rp
        JOIN Permissions p ON rp.permission_id = p.permission_id
        WHERE rp.role_id = ? AND p.permission_id = ?
    ");
    $stmt->bind_param("ii", $role_id, $permission_id);
    $stmt->execute();
    $stmt->store_result();

    // Check if the permission is found for the role
    $has_permission = $stmt->num_rows > 0;
    $stmt->close();

    return $has_permission;
}
?>
