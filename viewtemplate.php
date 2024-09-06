<?php
session_start();
require_once 'init.php';

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch the username from the session
$username = $_SESSION['username'];
$welcome_message = htmlspecialchars($username);

// Display the welcome message
echo "Welcome, " . $welcome_message;

require_once 'check_permission.php';

// Check if the user has permission to manage users
if (!check_permission($conn, 'view_templates')) {
    header("Location: access_denied.html");
    exit();
}

// Database connection
require_once 'config.php';
require_once 'conn.php';

// Query to fetch notification templates along with creator details
$query = "
    SELECT nt.noti_template_id, u.fullname, r.roleName, nt.noti_name, nt.noti_template
    FROM NotificationTemplates nt
    JOIN Users u ON nt.template_creator_id = u.user_id
    JOIN Roles r ON u.role_id = r.role_id
";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'head.html'; ?>
</head>

<body>

    <?php include 'admin-body.php'; ?>
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Notification Templates</h1>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>Creator Name</th>
                                        <th>Creator Role</th>
                                        <th>Title</th>
                                        <th>Template</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['roleName']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['noti_name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['noti_template']) . "</td>";
                                            echo "<td><i class='bi bi-trash' onclick='deleteTemplate(" . $row['noti_template_id'] . ")'></i></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <!-- End Table with stripped rows -->
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->


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

    <script>
        function deleteTemplate(templateId) {
            if (confirm('Are you sure? Do you want to delete the template permanently?')) {
                // Make an AJAX request to delete the template
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_template.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert('Template deleted successfully');
                        location.reload(); // Refresh the page to reflect the changes
                    }
                };
                xhr.send("noti_template_id=" + templateId);
            }
        }
    </script>
</body>

</html>
