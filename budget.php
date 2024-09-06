<?php
session_start();

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
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

// Handle form submission for adding new budget
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_budget'])) {
    $category_id = $_POST['category_id'];
    $amount_limit = $_POST['amount_limit'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Insert the new budget entry into the Budgets table
    $insert_query = "INSERT INTO Budgets (user_id, category_id, amount_limit, month, year) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iidii", $user_id, $category_id, $amount_limit, $month, $year);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error_message = "You have already added the limit for this category. Edit it in the table.";
            echo "<script>console.error('$error_message'); window.location.href = 'budget.php';</script>";
        } else {
            $error_message = "Failed to add the budget entry.";
        }
    }

    $stmt->close();
}

// Handle form submission for editing budget
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_budget'])) {
    $category_id = $_POST['category_id'];
    $amount_limit = $_POST['amount_limit'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Update the budget entry in the Budgets table
    $update_query = "UPDATE Budgets SET amount_limit = ? WHERE user_id = ? AND category_id = ? AND month = ? AND year = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("diiii", $amount_limit, $user_id, $category_id, $month, $year);

    if ($stmt->execute()) {
        // Success message or redirection can be added here if needed
        echo "<script>window.location.href = 'budget.php';</script>";
    } else {
        $error_message = "Failed to update the budget entry.";
    }

    $stmt->close();
}

// Handle form submission for deleting budget
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_budget'])) {
    $category_id = $_POST['category_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];

    // Delete the budget entry from the Budgets table
    $delete_query = "DELETE FROM Budgets WHERE user_id = ? AND category_id = ? AND month = ? AND year = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("iiii", $user_id, $category_id, $month, $year);

    if ($stmt->execute()) {
        // Success message or redirection can be added here if needed
        echo "<script>window.location.href = 'budget.php';</script>";
    } else {
        $error_message = "Failed to delete the budget entry.";
    }

    $stmt->close();
}

// Handle form submission for setting color thresholds
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['set_thresholds']) || isset($_POST['update_thresholds']))) {
    $green_end = $_POST['yellow_end'];
    $yellow_end = $_POST['green_end'];
    $orange_end = $_POST['orange_end'];
    


    $orange_start = $yellow_end + 1;
    $red_start = $orange_end + 1;
    $yellow_start = $green_end + 1;


    // Insert or update the colorcode entry for the user
    $insert_query = "INSERT INTO colorcode (user_id, startpercentage, endpercentage, interpercentage) VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE startpercentage = VALUES(startpercentage), endpercentage = VALUES(endpercentage), interpercentage = VALUES(interpercentage)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiii", $user_id, $orange_start, $orange_end, $yellow_start);

    if ($stmt->execute()) {
        // Success message or redirection can be added here if needed
    } else {
        $error_message = "Failed to set the color thresholds.";
    }

    $stmt->close();
}

// Get the selected month and year, or default to the current month and year
$month = isset($_POST['month']) ? $_POST['month'] : date('n');
$year = isset($_POST['year']) ? $_POST['year'] : date('Y');

// Fetch categories for the dropdown
$category_query = "SELECT category_id, category_name, user_id FROM Categories WHERE user_id = $user_id OR user_id IS NULL";
$category_result = $conn->query($category_query);

// Fetch budget, expenses, and remaining amounts for the selected month and year
$query = "
    SELECT 
        c.category_name AS category,
        b.category_id,
        b.amount_limit AS budget_amount,
        COALESCE(SUM(e.amount), 0) AS total_expense,
        b.amount_limit - COALESCE(SUM(e.amount), 0) AS remaining_amount
    FROM 
        Budgets b
    JOIN 
        Categories c ON b.category_id = c.category_id
    LEFT JOIN 
        Expense e ON b.category_id = e.category_id AND b.user_id = e.user_id AND MONTH(e.date) = b.month AND YEAR(e.date) = b.year
    WHERE 
        b.user_id = ? AND b.month = ? AND b.year = ?
    GROUP BY 
        c.category_name, b.category_id, b.amount_limit, b.month, b.year";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

$bills_data = [];
while ($row = $result->fetch_assoc()) {
    $bills_data[] = $row;
}
// Fetch budget, expenses, and remaining amounts for the selected month and year
$query = "
    SELECT 
        c.category_name AS category,
        b.category_id,
        b.amount_limit AS budget_amount,
        COALESCE(SUM(e.amount), 0) AS total_expense,
        b.amount_limit - COALESCE(SUM(e.amount), 0) AS remaining_amount,
        (COALESCE(SUM(e.amount), 0) / b.amount_limit) * 100 AS expense_percentage
    FROM 
        Budgets b
    JOIN 
        Categories c ON b.category_id = c.category_id
    LEFT JOIN 
        Expense e ON b.category_id = e.category_id AND b.user_id = e.user_id AND MONTH(e.date) = b.month AND YEAR(e.date) = b.year
    WHERE 
        b.user_id = ? AND b.month = ? AND b.year = ?
    GROUP BY 
        c.category_name, b.category_id, b.amount_limit, b.month, b.year";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

$bills_data = [];
while ($row = $result->fetch_assoc()) {
    $bills_data[] = $row;
}

$stmt->close();



// Fetch the user's color thresholds
$color_query = "SELECT startpercentage, endpercentage, interpercentage FROM colorcode WHERE user_id = ?";
$stmt = $conn->prepare($color_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($startpercentage, $endpercentage, $interpercentage);
$stmt->fetch();
$stmt->close();

$green_start = 0;
$green_end = $startpercentage - 1;
$yellow_start = $startpercentage;
$yellow_end = $interpercentage - 1;
$orange_start = $interpercentage;
$orange_end = $endpercentage;
$red_start = $endpercentage + 1;
$red_end = 100;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include 'head.html'; ?>
</head>

<body>
<?php include 'body.php'; ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Budget</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="userdashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Budget</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <!-- Color Thresholds Form -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Set Color Thresholds</h5>
        <form method="POST" action="budget.php">
          <div class="row mb-3">
            <label for="green_start" class="col-sm-2 col-form-label">Green</label>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="green_start" name="green_start" value="<?php echo $green_start; ?>" readonly>
            </div>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="green_end" name="green_end" value="<?php echo $green_end; ?>" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="yellow_start" class="col-sm-2 col-form-label">White</label>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="yellow_start" name="yellow_start" value="<?php echo $yellow_start; ?>" readonly>
            </div>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="yellow_end" name="yellow_end" value="<?php echo $yellow_end; ?>" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="orange_start" class="col-sm-2 col-form-label">Orange</label>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="orange_start" name="orange_start" value="<?php echo $orange_start; ?>" readonly>
            </div>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="orange_end" name="orange_end" value="<?php echo $orange_end; ?>" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="red_start" class="col-sm-2 col-form-label">Red</label>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="red_start" name="red_start" value="<?php echo $red_start; ?>" readonly>
            </div>
            <div class="col-sm-3">
              <input type="number" class="form-control" id="red_end" name="red_end" value="<?php echo $red_end; ?>" readonly>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-10 offset-sm-2">
              <button type="submit" class="btn btn-primary" name="<?php echo isset($startpercentage) ? 'update_thresholds' : 'set_thresholds'; ?>">
                <?php echo isset($startpercentage) ? 'Update Thresholds' : 'Set Thresholds'; ?>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Budget Form -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Add Budget</h5>
        <form method="POST" action="budget.php">
          <div class="row mb-3">
            <label for="category_id" class="col-sm-2 col-form-label">Category</label>
            <div class="col-sm-10">
              <select class="form-select" id="category_id" name="category_id" required>
                <?php while ($category_row = $category_result->fetch_assoc()): ?>
                  <option value="<?php echo $category_row['category_id']; ?>">
                    <?php echo htmlspecialchars($category_row['category_name']); ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="amount_limit" class="col-sm-2 col-form-label">Amount Limit</label>
            <div class="col-sm-10">
              <input type="number" class="form-control" id="amount_limit" name="amount_limit" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="month" class="col-sm-2 col-form-label">Month</label>
            <div class="col-sm-10">
              <select class="form-select" id="month" name="month" required>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                  <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                  </option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="year" class="col-sm-2 col-form-label">Year</label>
            <div class="col-sm-10">
              <input type="number" class="form-control" id="year" name="year" value="<?php echo $year; ?>" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-10 offset-sm-2">
              <button type="submit" class="btn btn-primary" name="add_budget">Add Budget</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Budget Overview Table -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Budget Limit</h5>

        <form method="POST" action="budget.php">
          <div class="row mb-3">
            <label for="month" class="col-sm-2 col-form-label">Select Month</label>
            <div class="col-sm-10">
              <select class="form-select" id="month" name="month" required>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                  <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
                  </option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label for="year" class="col-sm-2 col-form-label">Select Year</label>
            <div class="col-sm-10">
              <input type="number" class="form-control" id="year" name="year" value="<?php echo $year; ?>" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-sm-10 offset-sm-2">
              <button type="submit" class="btn btn-primary">Filter</button>
            </div>
          </div>
        </form>

        <table class="table table-bordered">
    <thead>
        <tr>
            <th>Category</th>
            <th>Budget Amount</th>
            <th>Total Expense</th>
            <th>Remaining Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($bills_data as $bill): ?>
            <?php
                $expense_percentage = $bill['expense_percentage'];
                if ($expense_percentage <= $startpercentage) {
                    $row_class = 'table-success'; // Green
                }
                elseif ($expense_percentage <= $interpercentage) {
                  $row_class = 'table-warning1'; // yellow
                } 
                elseif ($expense_percentage <= $endpercentage) {
                    $row_class = 'table-warning'; // Orange
                } else {
                    $row_class = 'table-danger'; // Red
                }
            ?>
            <tr class="<?php echo $row_class; ?>">
                <td><?php echo htmlspecialchars($bill['category']); ?></td>
                <td><?php echo number_format($bill['budget_amount'], 2); ?></td>
                <td><?php echo number_format($bill['total_expense'], 2); ?></td>
                <td><?php echo number_format($bill['remaining_amount'], 2); ?></td>
                <td>
                    <!-- Edit Budget Form -->
                    <form method="POST" action="budget.php" class="d-inline">
                        <input type="hidden" name="category_id" value="<?php echo $bill['category_id']; ?>">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editBudgetModal<?php echo $bill['category_id']; ?>">
                        <i class="bi bi-pencil"></i>
                        </button>
                        <div class="modal fade" id="editBudgetModal<?php echo $bill['category_id']; ?>" tabindex="-1" aria-labelledby="editBudgetModalLabel<?php echo $bill['category_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editBudgetModalLabel<?php echo $bill['category_id']; ?>">Edit Budget</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="amount_limit" class="form-label">Amount Limit</label>
                                            <input type="number" class="form-control" id="amount_limit" name="amount_limit" value="<?php echo $bill['budget_amount']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" name="edit_budget">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Delete Budget Form -->
                    <form method="POST" action="budget.php" class="d-inline">
                        <input type="hidden" name="category_id" value="<?php echo $bill['category_id']; ?>">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" name="delete_budget">
                      <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


      </div>
    </div>

  </main><!-- End #main -->



    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

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
