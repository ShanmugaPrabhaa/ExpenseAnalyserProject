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

// Fetch budget data for the pie chart
$query = "
    SELECT 
        c.category_name AS category,
        b.amount_limit AS budget_amount
    FROM 
        Budgets b
    JOIN 
        Categories c ON b.category_id = c.category_id
    WHERE 
        b.user_id = ? AND b.month = ? AND b.year = ?";

$month = date('n'); // Current month as integer (1-12)
$year = date('Y');  // Current year as integer

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
$amounts = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = htmlspecialchars($row['category']);
    $amounts[] = $row['budget_amount'];
}

$stmt->close();
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
        <h1>Budget Report</h1>
    </div><!-- End Page Title -->

    <div class="col-lg-6">
        <div class="card">
        <div class="card-body">
                    <h5 class="card-title">Pie Chart</h5>

                    <!-- Pie Chart -->
                    <canvas id="pieChart" style="max-height: 400px;"></canvas>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            new Chart(document.querySelector('#pieChart'), {
                                type: 'pie',
                                data: {
                                    labels: <?php echo json_encode($categories); ?>,
                                    datasets: [{
                                        label: 'Expenses by Category',
                                        data: <?php echo json_encode($amounts); ?>,
                                        backgroundColor: [
                                            'rgb(255, 99, 132)',
                                            'rgb(54, 162, 235)',
                                            'rgb(255, 205, 86)',
                                            'rgb(75, 192, 192)',
                                            'rgb(153, 102, 255)',
                                            'rgb(255, 159, 64)'
                                        ],
                                        hoverOffset: 4
                                    }]
                                }
                            });
                        });
                    </script>
                    <!-- End Pie Chart -->

                </div>
        </div>
    </div>

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