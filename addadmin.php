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
echo $welcome_message;

require_once 'check_permission.php';

// Check if the user has permission to manage users
if (!check_permission($conn, 'add_admins')) {
    header("Location: access_denied.html");
    exit();
}

// Database connection
require_once 'config.php';
require_once 'conn.php';

// Fetch categories for the dropdown
$role_query = "SELECT role_id, roleName FROM Roles WHERE role_id != 1";
$role_result = $conn->query($role_query);
$adder_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['adminId'] ?? '';
    $username = $_POST['userName'];
    $fullname = $_POST['fullName'];
    $email = $_POST['email'];
    $role_id = $_POST['roleName'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (isset($_POST['save'])) {
        $stmt = $conn->prepare("INSERT INTO adminbuffer (adder_id,username, password_hash, email, fullname, role_id) VALUES ($adder_id ,?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $username, $password, $email, $fullname, $role_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        $stmt = $conn->prepare("UPDATE adminbuffer SET username = ?, password_hash = ?, email = ?, fullname = ?, role_id = ? WHERE admin_id = ?");
        $stmt->bind_param("ssssii", $username, $password, $email, $fullname, $role_id, $admin_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['drop'])) {
        $stmt = $conn->prepare("DELETE FROM adminbuffer WHERE admin_id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch data for pagination
$admins_query = "SELECT * FROM adminbuffer";
$admins_result = $conn->query($admins_query);
$admins = [];
while ($row = $admins_result->fetch_assoc()) {
    $admins[] = $row;
}
$total_pages = 50;
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
      <h1>Add New Admin</h1>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-6" style="width: 100%;">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Add New Admin</h5>

              <!-- General Form Elements -->
              <form id="adminForm" method="POST" action="" style="width: 100%;">
                <input type="hidden" id="adminId" name="adminId" value="">
                <div class="row mb-3">
                    <label for="userName" class="col-sm-2 col-form-label">User Name</label>
                    <div class="col-sm-10">
                        <input type="text" id="userName" name="userName" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="fullName" class="col-sm-2 col-form-label">Full Name</label>
                    <div class="col-sm-10">
                        <input type="text" id="fullName" name="fullName" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                  <label for="roleName" class="col-sm-2 col-form-label">Role</label>
                  <div class="col-sm-10">
                    <select id="roleName" name="roleName" class="form-select" aria-label="Default select example" required>
                      <?php while ($role = $role_result->fetch_assoc()) { ?>
                        <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['roleName']); ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                    <label for="password" class="col-sm-2 col-form-label">Default Password</label>
                    <div class="col-sm-10">
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-10 d-flex justify-content-center">
                        <button type="submit" name="save" class="btn btn-primary mx-1">Save</button>
                        <button type="submit" name="update" class="btn btn-primary mx-1">Update</button>
                        <button type="submit" name="drop" class="btn btn-primary mx-1">Drop</button>
                        <button type="button" id="previousBtn" class="btn btn-primary mx-1">Previous</button>
                        <button type="button" id="nextBtn" class="btn btn-primary mx-1">Next</button>
                    </div>
                </div>
              </form>
            <!-- Page Number Display -->
            <div id="pageNumber" class="text-center mt-3">Page: 1 of 50</div>
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
    let admins = <?php echo json_encode($admins); ?>;
    let currentIndex = 0;
    const totalPages = 50;

    function updateFormDisplay(index) {
        if (index >= 0 && index < admins.length) {
            document.getElementById('adminId').value = admins[index].admin_id;
            document.getElementById('userName').value = admins[index].username;
            document.getElementById('fullName').value = admins[index].fullname;
            document.getElementById('email').value = admins[index].email;
            document.getElementById('roleName').value = admins[index].role_id;
            document.getElementById('password').value = ''; // Do not display the password
        } else {
            document.getElementById('adminForm').reset();
            document.getElementById('adminId').value = '';
        }
        document.getElementById('pageNumber').textContent = 'Page: ' + (index + 1) + ' of ' + totalPages;
    }

    document.getElementById('previousBtn').addEventListener('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            updateFormDisplay(currentIndex);
        }
    });

    document.getElementById('nextBtn').addEventListener('click', function() {
        if (currentIndex < totalPages - 1) {
            currentIndex++;
            updateFormDisplay(currentIndex);
        }
    });

    updateFormDisplay(currentIndex);
  </script>
  
</body>
</html>
