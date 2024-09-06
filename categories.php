<?php
session_start();
require_once 'conn.php';

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch the username from the session
$username = $_SESSION['username'];

// Fetch categories for the logged-in user or null user_id categories
$user_id = $_SESSION['user_id'];
$categories_query = "SELECT category_id, category_name, user_id FROM Categories WHERE user_id = $user_id OR user_id IS NULL";
$categories_result = mysqli_query($conn, $categories_query);

// Function to display categories in HTML
function displayCategories($categories_result, $user_id) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
        echo '<td>';
        if ($row['user_id'] == $user_id) {
            echo '<form method="post" style="display:inline-block;">';
            echo '<input type="hidden" name="category_id" value="' . $row['category_id'] . '">';
            echo '<button type="submit" name="delete_category" style="border:none; background:none;">';
            echo '<i class="bi bi-trash"></i>';
            echo '</button>';
            echo '</form>';
        }
        echo '</td>';
        echo '</tr>';
    }
}

// Handle adding a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $new_category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    // Insert new category with logged-in user's user_id
    $insert_query = "INSERT INTO Categories (category_name, user_id) VALUES ('$new_category_name', $user_id)";
    mysqli_query($conn, $insert_query);
    header("Location: ".$_SERVER['PHP_SELF']); // Refresh the page after adding category
    exit();
}

// Handle deleting a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    
    // Delete category only if the logged-in user added it (user_id matches)
    $delete_query = "DELETE FROM Categories WHERE category_id = $category_id AND user_id = $user_id";
    mysqli_query($conn, $delete_query);
    header("Location: ".$_SERVER['PHP_SELF']); // Refresh the page after deleting category
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.html'; ?>
</head>

<body>
    <?php include 'body.php'; ?>
    <main id="main" class="main">
        <div class="container">
            
            <h3>Categories</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php displayCategories($categories_result, $user_id); ?>
                </tbody>
            </table>

            <!-- Add new category form -->
            <h3>Add New Category</h3>
            <form method="post">
                <div class="form-group">
                    <label for="category_name">Category Name</label>
                    <input type="text" id="category_name" name="category_name" class="form-control" required>
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            </form>
        </div>
    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/chart.js/chart.umd.js"></script>
    <script src="assets/vendor/echarts/echarts.min.js"></script>
    <script src="assets/vendor/quill/quill.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>
