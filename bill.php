<?php
session_start();

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Fetch the username from the session
$username = $_SESSION['username'];
$welcome_message = htmlspecialchars($username);

// Database connection
require_once 'config.php';
require_once 'conn.php';

$user_id = $_SESSION['user_id'];

// Fetch bill data
$query = "SELECT bill_id, bill_name, amount_due, due_date, reminder_date, reminders FROM Bills WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bills = [];
while ($row = $result->fetch_assoc()) {
    $bills[] = $row;
}
$stmt->close();

// Handle toggle reminder
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_reminder'])) {
    $bill_id = $_POST['bill_id'];
    $current_reminder = $_POST['current_reminder'];
    $new_reminder = $current_reminder == 1 ? 0 : 1;

    $query = "UPDATE Bills SET reminders = ? WHERE bill_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $new_reminder, $bill_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle delete bill
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_bill'])) {
    $bill_id = $_POST['bill_id'];

    $query = "DELETE FROM Bills WHERE bill_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $bill_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle add new bill
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_bill'])) {
    $bill_name = $_POST['bill_name'];
    $amount_due = $_POST['amount_due'];
    $due_date = $_POST['due_date'];
    $reminder_date = $_POST['reminder_date'];
    $currentDate = date('Y-m-d'); // Get current date

    if (strtotime($reminder_date) > strtotime($due_date)) {
        echo "<script>alert('Reminder date cannot be later than the due date.');</script>";
    } 
    else if (strtotime($reminder_date) < strtotime($currentDate) || strtotime($due_date) < strtotime($currentDate)) {
        echo "<script>alert('Due date or reminder date has already passed.');</script>";
    }
    else {
        $query = "INSERT INTO Bills (user_id, bill_name, amount_due, due_date, reminder_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isdss", $user_id, $bill_name, $amount_due, $due_date, $reminder_date);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'head.html'; ?>

  <script>
    function validateForm() {
      const dueDate = new Date(document.getElementById('due_date').value);
      const reminderDate = new Date(document.getElementById('reminder_date').value);

      if (reminderDate > dueDate) {
        alert('Reminder date cannot be later than the due date.');
        return false;
      }
      return true;
    }
  </script>
</head>

<body>

<?php include 'body.php'; ?>
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Bill Reminders</h1>
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
                  <th scope="col">Bill Name</th>
                  <th scope="col">Amount Due</th>
                  <th scope="col">Due Date</th>
                  <th scope="col">Reminder Date</th>
                  <th scope="col">Reminder</th>
                  <th scope="col">Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($bills as $bill): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($bill['bill_name']); ?></td>
                    <td><?php echo htmlspecialchars($bill['amount_due']); ?></td>
                    <td><?php echo htmlspecialchars($bill['due_date']); ?></td>
                    <td><?php echo htmlspecialchars($bill['reminder_date']); ?></td>
                    <td>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="bill_id" value="<?php echo $bill['bill_id']; ?>">
                        <input type="hidden" name="current_reminder" value="<?php echo $bill['reminders']; ?>">
                        <button type="submit" name="toggle_reminder" style="border:none; background:none;">
                          <i class="bi <?php echo $bill['reminders'] ? 'bi-bell-fill' : 'bi-bell-slash'; ?>"></i>
                        </button>
                      </form>
                    </td>
                    <td>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="bill_id" value="<?php echo $bill['bill_id']; ?>">
                        <button type="submit" name="delete_bill" style="border:none; background:none;">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <!-- End Table with stripped rows -->

              <!-- Add New Bill -->
              <h5>Add New Bill</h5>
              <form method="post" onsubmit="return validateForm();">
                <div class="mb-3">
                  <label for="bill_name" class="form-label">Bill Name</label>
                  <input type="text" class="form-control" id="bill_name" name="bill_name" required>
                </div>
                <div class="mb-3">
                  <label for="amount_due" class="form-label">Amount Due</label>
                  <input type="number" class="form-control" id="amount_due" name="amount_due" step="0.01" required>
                </div>
                <div class="mb-3">
                  <label for="due_date" class="form-label">Due Date</label>
                  <input type="date" class="form-control" id="due_date" name="due_date" required>
                </div>
                <div class="mb-3">
                  <label for="reminder_date" class="form-label">Reminder Date</label>
                  <input type="date" class="form-control" id="reminder_date" name="reminder_date" required>
                </div>
                <button type="submit" name="add_bill" class="btn btn-primary">Add Bill</button>
              </form>
              <!-- End Add New Bill -->

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