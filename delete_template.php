<?php
session_start();

require_once 'config.php';
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['noti_template_id'])) {
    $template_id = $_POST['noti_template_id'];
    $admin_id = $_SESSION['user_id'];

    // Begin transaction
    $conn->begin_transaction();

    try {

        // Delete the template from NotificationTemplates table
        $stmt = $conn->prepare("DELETE FROM NotificationTemplates WHERE noti_template_id = ?");
        $stmt->bind_param("i", $template_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting from NotificationTemplates: " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo "Template deleted successfully";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error deleting template: " . $e->getMessage();
    }
}
$conn->close();
?>
