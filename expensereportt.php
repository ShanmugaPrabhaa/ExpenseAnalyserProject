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

// Get the selected month and year from the query parameters or default to the current month and year
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch expense data for the pie chart
$user_id = $_SESSION['user_id'];
$query = "SELECT c.category_name, SUM(e.amount) as total_amount 
          FROM Expense e
          JOIN Categories c ON e.category_id = c.category_id
          WHERE e.user_id = ? AND YEAR(e.date) = ? AND MONTH(e.date) = ?
          GROUP BY e.category_id";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $user_id, $current_year, $current_month);
$stmt->execute();
$result = $stmt->get_result();

$categories = [];
$amounts = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category_name'];
    $amounts[] = $row['total_amount'];
}
$stmt->close();

// Fetch daily expense data for the line chart
$query = "SELECT DAY(date) as day, SUM(amount) as daily_total 
          FROM Expense
          WHERE user_id = ? AND YEAR(date) = ? AND MONTH(date) = ?
          GROUP BY DAY(date)";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $user_id, $current_year, $current_month);
$stmt->execute();
$result = $stmt->get_result();

$days = range(1, cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year));
$daily_totals = array_fill(0, count($days), 0);
while ($row = $result->fetch_assoc()) {
    $daily_totals[$row['day'] - 1] = $row['daily_total'];
}
$stmt->close();

// If there are no daily totals, navigate to no_data.html
if (empty(array_filter($daily_totals))) {
    header("Location: no_data.html");
    exit();
}

// Calculate average expenses for the current month and year
$total_expense_current_month = array_sum($daily_totals);
$average_expense_current_month = $total_expense_current_month / count(array_filter($daily_totals));

// Calculate linear regression coefficients (least squares method)
$n = count($days);
$sum_x = array_sum($days);
$sum_y = array_sum($daily_totals);
$sum_xy = 0;
$sum_xx = 0;
for ($i = 0; $i < $n; $i++) {
    $sum_xy += ($days[$i] * $daily_totals[$i]);
    $sum_xx += ($days[$i] * $days[$i]);
}

$slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_xx - $sum_x * $sum_x);
$intercept = ($sum_y - $slope * $sum_x) / $n;

// Predicted expenses for current month (linear regression)
$predicted_expenses = [];
for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year); $day++) {
    $predicted_expense = $slope * $day + $intercept;
    $predicted_expenses[] = max($predicted_expense, 0); // Ensure no negative predictions
}

// Predict expenses for next month
$next_month = ($current_month % 12) + 1;
$next_year = $current_month == 12 ? $current_year + 1 : $current_year;

$predicted_expenses_next_month = [];
for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $next_month, $next_year); $day++) {
    $predicted_expense = $slope * $day + $intercept;
    $predicted_expenses_next_month[] = max($predicted_expense, 0); // Ensure no negative predictions
}

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
            <h1>Expense Report</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item">Expense Report</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <div class="row">
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

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Line Chart</h5>

                        <!-- Line Chart -->
                        <div id="lineChart"></div>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                new ApexCharts(document.querySelector("#lineChart"), {
                                    series: [{
                                        name: "Daily Expenses",
                                        data: <?php echo json_encode($daily_totals); ?>
                                    }, {
                                        name: "Predicted Expenses",
                                        data: <?php echo json_encode($predicted_expenses); ?>
                                    }],
                                    chart: {
                                        height: 350,
                                        type: 'line',
                                        zoom: {
                                            enabled: false
                                        }
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        curve: 'straight'
                                    },
                                    grid: {
                                        row: {
                                            colors: ['#f3f3f3', 'transparent'],
                                            opacity: 0.5
                                        },
                                    },
                                    xaxis: {
                                        categories: <?php echo json_encode($days); ?>
                                    }
                                }).render();
                            });
                        </script>
                        <!-- End Line Chart -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Average and Predicted Expenses</h5>
                        <p><strong>Total Expenses for Current Month:</strong> <?php echo number_format($total_expense_current_month, 2); ?></p>
                        <p><strong>Average Daily Expenses for Current Month:</strong> <?php echo number_format($average_expense_current_month, 2); ?></p>
                        <p><strong>Predicted Expenses for Next Month:</strong> <?php echo number_format(array_sum($predicted_expenses_next_month), 2); ?></p>
                    </div>
                </div>
            </div>
        </div>


    <!-- Add navigation buttons for previous and next month -->
    <div class="row">
        <div class="col-lg-6">
            <button id="prevMonth" class="btn btn-primary">Previous Month</button>
        </div>
        <div class="col-lg-6 text-right">
            <button id="nextMonth" class="btn btn-primary">Next Month</button>
        </div>
    </div>

    <script>
        document.getElementById('prevMonth').addEventListener('click', () => {
            changeMonth(-1);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            changeMonth(1);
        });

        function changeMonth(offset) {
            const currentMonth = parseInt("<?php echo $current_month; ?>");
            const currentYear = parseInt("<?php echo $current_year; ?>");

            let newMonth = currentMonth + offset;
            let newYear = currentYear;

            if (newMonth > 12) {
                newMonth = 1;
                newYear++;
            } else if (newMonth < 1) {
                newMonth = 12;
                newYear--;
            }

            window.location.href = `expensereport.php?month=${newMonth}&year=${newYear}`;
        }
    </script>
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