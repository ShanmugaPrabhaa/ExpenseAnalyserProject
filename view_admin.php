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

// Query to fetch user details with role_id = 1
$sql = "SELECT u.user_id,u.username, u.email, u.fullname, u.created_at, r.roleName 
        FROM Users u 
        left join roles r on r.role_id = u.role_id
        where u.role_id !=1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $users = [];
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
  <h1>Admin - DataBase</h1>
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
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo htmlspecialchars($user['roleName']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                <td>
                  <form method="post" action="delete_user.php" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </td>
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
