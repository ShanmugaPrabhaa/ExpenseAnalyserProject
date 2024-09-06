<?php
session_start();
require_once 'init.php';

// Check if the user is logged in and session ID matches
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id']) || session_id() !== $_SESSION['session_id']) {
    // If session ID doesn't match or user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Handling date navigation
$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_date_timestamp = strtotime($current_date);

// Previous and Next date buttons
$prev_date = date('Y-m-d', strtotime('-1 day', $current_date_timestamp));
$next_date = date('Y-m-d', strtotime('+1 day', $current_date_timestamp));

// Fetch expenses for the logged-in user for the current date
$expenses_query = "SELECT e.ex_id, e.amount, e.description, e.date, e.category_id, c.category_name 
                   FROM Expense e
                   JOIN Categories c ON e.category_id = c.category_id
                   WHERE e.user_id = $user_id AND e.date = '$current_date'";
$expenses_result = mysqli_query($conn, $expenses_query);

// Fetch categories for the dropdown
$categories_query = "SELECT category_id, category_name, user_id FROM Categories WHERE user_id = $user_id OR user_id IS NULL";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch budgets for the logged-in user
$budgets_query = "SELECT category_id, amount_limit FROM Budgets WHERE user_id = $user_id AND month = MONTH(CURDATE()) AND year = YEAR(CURDATE())";
$budgets_result = mysqli_query($conn, $budgets_query);
$budgets = [];
while ($row = mysqli_fetch_assoc($budgets_result)) {
    $budgets[$row['category_id']] = $row['amount_limit'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $expense_id = $_POST['expense_id'];
        $delete_query = "DELETE FROM Expense WHERE ex_id = $expense_id";
        mysqli_query($conn, $delete_query);
        header("Location: expense.php?date=$current_date");
    } elseif (isset($_POST['add_expense'])) {
        $category_id = $_POST['category_id'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $add_query = "INSERT INTO Expense (user_id, category_id, amount, description, date) 
              VALUES ($user_id, $category_id, $amount, '$description', '$date')";

        mysqli_query($conn, $add_query);
        header("Location: expense.php?date=$current_date");
    } elseif (isset($_POST['edit_expense'])) {
        $expense_id = $_POST['expense_id'];
        $category_id = $_POST['category_id'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $edit_query = "UPDATE Expense SET category_id = $category_id, amount = $amount, description = '$description', date = '$date' WHERE ex_id = $expense_id";
        mysqli_query($conn, $edit_query);
        header("Location: expense.php?date=$current_date");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'head.html'; ?>
  <style>
  .dot {
    height: 15px;
    width: 15px;
    border-radius: 50%;
    display: inline-block;
  }
  </style>
</head>
<body>
<?php include 'body.php'; ?>
<main id="main" class="main">
<div class="pagetitle">
    <h1>Expenses for <?php echo $current_date; ?></h1>
    <form method="get" style="display: inline-block;">
      <label for="date">Select Date: </label>
      <input type="date" id="date" name="date" value="<?php echo $current_date; ?>" required>
      <button type="submit" class="btn btn-primary">Go</button>
    </form>
</div>
<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <table class="table datatable">
            <thead>
              <tr>
                <th>Category</th>
                <th>Spent Amount</th>
                <th>Description</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($expenses_result)): 
                $category_id = $row['category_id'];
                $amount = $row['amount'];
                $budget_limit = isset($budgets[$category_id]) ? $budgets[$category_id] : 0;
                $color = 'grey';
                if ($budget_limit > 0) {
                    $percentage = ($amount / $budget_limit) * 100;
                    if ($percentage < 50) $color = 'green';
                    elseif ($percentage < 70) $color = 'orange';
                    elseif ($percentage >= 80) $color = 'red';
                }
              ?>
              <tr>
                <td><?php echo $row['category_name']; ?></td>
                <td><?php echo $amount; ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo $row['date']; ?></td>
                <td>
                  <form method="post" style="display:inline-block;">
                    <input type="hidden" name="expense_id" value="<?php echo $row['ex_id']; ?>">
                    <button type="submit" name="delete" style="border:none; background:none;">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                  <button class="btn btn-primary btn-sm" onclick="editExpense(<?php echo htmlspecialchars(json_encode($row)); ?>)"><i class="bi bi-pencil"></i></button>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <button class="btn btn-success" onclick="addNewExpense()">Add New</button>
      <a class="btn btn-primary" href="expense.php?date=<?php echo $prev_date; ?>">Previous</a>
      <a class="btn btn-primary" href="expense.php?date=<?php echo $next_date; ?>" <?php if ($next_date > date('Y-m-d')) echo 'style="display:none;"'; ?>>Next</a>
      <div id="expense-form" style="display:none;">
        <form method="post">
          <input type="hidden" name="expense_id" id="expense_id">
          <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control">
              <?php 
              // Re-fetch categories for the dropdown as the initial categories result has been consumed
              $categories_result = mysqli_query($conn, $categories_query);
              while ($category = mysqli_fetch_assoc($categories_result)): ?>
              <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <input type="text" name="description" id="description" class="form-control">
          </div>
          <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" class="form-control" value="<?php echo $current_date; ?>" required>
          </div>
          <button type="submit" name="add_expense" class="btn btn-success">Save</button>
          <button type="submit" name="edit_expense" class="btn btn-primary">Update</button>
        </form>
      </div>
    </div>
  </div>
</section>
</main>
<script>
function addNewExpense() {
  document.getElementById('expense-form').style.display = 'block';
  document.querySelector('button[name="add_expense"]').style.display = 'inline-block';
  document.querySelector('button[name="edit_expense"]').style.display = 'none';
  document.getElementById('expense_id').value = '';
  document.getElementById('category_id').value = '';
  document.getElementById('amount').value = '';
  document.getElementById('description').value = '';
  document.getElementById('date').value = '<?php echo $current_date; ?>';
}

function editExpense(expense) {
  document.getElementById('expense-form').style.display = 'block';
  document.querySelector('button[name="add_expense"]').style.display = 'none';
  document.querySelector('button[name="edit_expense"]').style.display = 'inline-block';
  document.getElementById('expense_id').value = expense.ex_id;
  document.getElementById('category_id').value = expense.category_id;
  document.getElementById('amount').value = expense.amount;
  document.getElementById('description').value = expense.description;
  document.getElementById('date').value = expense.date;
}
</script>

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
