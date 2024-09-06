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
if (!check_permission($conn, 'add_admins')) {
    header("Location: access_denied.html");
    exit();
}

// Fetch details from adminbuffer table
$sql_adminbuffer = "SELECT u.admin_id, u.username, u.email, u.fullname, u.created_at, r.roleName, r.role_id
                    FROM adminbuffer u 
                    LEFT JOIN roles r ON r.role_id = u.role_id
                    WHERE u.role_id != 1";
$result_adminbuffer = mysqli_query($conn, $sql_adminbuffer);
$adminbuffer_users = mysqli_num_rows($result_adminbuffer) > 0 ? mysqli_fetch_all($result_adminbuffer, MYSQLI_ASSOC) : [];

// Fetch details from users table
$sql_users = "SELECT u.user_id, u.username, u.email, u.fullname, u.created_at, r.roleName 
              FROM Users u 
              LEFT JOIN roles r ON r.role_id = u.role_id
              WHERE u.role_id != 1";
$result_users = mysqli_query($conn, $sql_users);
$users = mysqli_num_rows($result_users) > 0 ? mysqli_fetch_all($result_users, MYSQLI_ASSOC) : [];

// Get list of committed usernames from Users table
$committed_usernames = array_column($users, 'username');

// Merge adminbuffer and users data into a single array
$all_users = [];
foreach ($adminbuffer_users as $user) {
    $user['status'] = in_array($user['username'], $committed_usernames) ? 'Committed' : '';
    $all_users[] = $user;
}
foreach ($users as $user) {
    $user['status'] = 'Committed';
    $all_users[] = $user;
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php include 'head.html'; ?>
</head>
<body>
    <?php include 'admin-body.php'; ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Admin-Buffer</h1>
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
                                        <th>Role Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Full name</th>
                                        <th data-type="date" data-format="YYYY/DD/MM">Created At</th>
                                        <th>Action</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['roleName']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                            <td>
                                                <?php if (empty($user['status'])): ?>
                                                    <form method="post" action="commit_user.php" style="display:inline;">
                                                        <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($user['admin_id']); ?>">
                                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                        <input type="hidden" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">
                                                        <input type="hidden" name="role_id" value="<?php echo htmlspecialchars($user['role_id']); ?>">
                                                        <button type="submit" class="btn btn-primary">Commit</button>
                                                    </form>
                                                <?php else: ?>
                                                    Committed
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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
</body>
</html>
