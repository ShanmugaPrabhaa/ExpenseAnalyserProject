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

// Display the welcome message
echo $welcome_message;

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

// Fetch expense data
$today = date('Y-m-d');
$startOfMonth = date('Y-m-01');
$startOfYear = date('Y-01-01');

// Prepare and execute queries to fetch total expenses
$queryToday = "SELECT SUM(amount) as total_today FROM Expense WHERE user_id = ? AND date = ?";
$queryMonth = "SELECT SUM(amount) as total_month FROM Expense WHERE user_id = ? AND date >= ?";
$queryYear = "SELECT SUM(amount) as total_year FROM Expense WHERE user_id = ? AND date >= ?";

$stmtToday = $conn->prepare($queryToday);
$stmtToday->bind_param("is", $user_id, $today);
$stmtToday->execute();
$resultToday = $stmtToday->get_result();
$totalToday = $resultToday->fetch_assoc()['total_today'] ?? 0;

$stmtMonth = $conn->prepare($queryMonth);
$stmtMonth->bind_param("is", $user_id, $startOfMonth);
$stmtMonth->execute();
$resultMonth = $stmtMonth->get_result();
$totalMonth = $resultMonth->fetch_assoc()['total_month'] ?? 0;

$stmtYear = $conn->prepare($queryYear);
$stmtYear->bind_param("is", $user_id, $startOfYear);
$stmtYear->execute();
$resultYear = $stmtYear->get_result();
$totalYear = $resultYear->fetch_assoc()['total_year'] ?? 0;

// Fetch total budget for the current month
$queryBudget = "SELECT SUM(amount_limit) as total_budget FROM Budgets WHERE user_id = ? AND month = ? AND year = ?";
$stmtBudget = $conn->prepare($queryBudget);
$stmtBudget->bind_param("iii", $user_id, $month, $year);
$stmtBudget->execute();
$resultBudget = $stmtBudget->get_result();
$totalBudget = $resultBudget->fetch_assoc()['total_budget'] ?? 0;

// Fetch total expenses for the current month
$queryExpenses = "SELECT SUM(amount) as total_expense FROM Expense WHERE user_id = ? AND date >= ? AND date <= ?";
$stmtExpenses = $conn->prepare($queryExpenses);
$stmtExpenses->bind_param("iss", $user_id, $startOfMonth, $today);
$stmtExpenses->execute();
$resultExpenses = $stmtExpenses->get_result();
$totalExpense = $resultExpenses->fetch_assoc()['total_expense'] ?? 0;

// Calculate the remaining amount
$remainingAmount = $totalBudget - $totalExpense;

// Query to get total expenses and remaining budget by date
$queryReports = "
    SELECT
        e.date,
        c.category_name,
        SUM(e.amount) AS total_expense,
        (b.amount_limit - IFNULL((SELECT SUM(e2.amount)
            FROM Expense e2
            WHERE e2.user_id = e.user_id
                AND e2.category_id = e.category_id
                AND EXTRACT(YEAR FROM e2.date) = b.year
                AND EXTRACT(MONTH FROM e2.date) = b.month
                AND e2.date <= e.date), 0)) AS total_budget
    FROM
        Expense e
    LEFT JOIN
        Budgets b ON e.user_id = b.user_id
            AND e.category_id = b.category_id
            AND EXTRACT(MONTH FROM e.date) = b.month
            AND EXTRACT(YEAR FROM e.date) = b.year
    LEFT JOIN
        Categories c ON e.category_id = c.category_id
    WHERE
        e.user_id = ? AND e.date >= ?
    GROUP BY
        e.date, c.category_name, b.amount_limit, b.month, b.year, e.user_id, e.category_id
    ORDER BY
        e.date ASC";

$stmtReports = $conn->prepare($queryReports);
$stmtReports->bind_param("is", $user_id, $startOfYear);
$stmtReports->execute();
$resultReports = $stmtReports->get_result();

$dates = [];
$expenses = [];
$budgets = [];
$categories = [];

while ($row = $resultReports->fetch_assoc()) {
    $dates[] = $row['date'];
    $expenses[] = $row['total_expense'];
    $budgets[] = $row['total_budget'];
    $categories[] = $row['category_name'];
}

$dates_json = json_encode($dates);
$expenses_json = json_encode($expenses);
$budgets_json = json_encode($budgets);
$categories_json = json_encode($categories);

$queryGoals = "
    SELECT goal_name, current_amount, target_amount, target_date 
    FROM Goals 
    WHERE user_id = ? 
    ORDER BY target_date ASC";
$stmtGoals = $conn->prepare($queryGoals);
$stmtGoals->bind_param("i", $user_id);
$stmtGoals->execute();
$resultGoals = $stmtGoals->get_result();

$goals = [];
while ($row = $resultGoals->fetch_assoc()) {
    $goals[] = $row;
}

// Fetch notifications for the current user
$queryNotifications = "
    SELECT 
        pn.reminder_id,
        pn.reminder_message
    FROM 
        billreminders pn
    
    WHERE 
        pn.user_id = ?
    ORDER BY 
        pn.created_at DESC";

$stmtNotifications = $conn->prepare($queryNotifications);
$stmtNotifications->bind_param("i", $user_id);
$stmtNotifications->execute();
$resultNotifications = $stmtNotifications->get_result();

$notifications = [];
while ($row = $resultNotifications->fetch_assoc()) {
    $notifications[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'head.html'; ?>
  <style>
    .delete-btn {
      border: none;
      margin-left : 200px;
      background-color: red;
      opacity: 0.4;

    }
    .delete-btn:hover{
      opacity: 0.9;
    }
    </style>

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="userdashboard.php" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">ExpenseAnalyser</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">
    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-bell"></i>
        <span class="badge bg-primary badge-number"><?php echo count($notifications); ?></span>
    </a><!-- End Notification Icon -->

    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
        <li class="dropdown-header">
            You have <?php echo count($notifications); ?> new notifications
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>
        
        <?php foreach ($notifications as $notification): ?>
            <li class="notification-item">
                <i class="bi bi-info-circle text-primary"></i>
                <div>
                    <h4><?php echo htmlspecialchars($notification['reminder_message']); ?></h4>
                    <!-- Delete button (trash icon) -->
                    <form action="delete_notification.php" method="post">
                        <input type="hidden" name="reminder_id" value="<?php echo $notification['reminder_id']; ?>">
                        <button type="submit" class="btn btn-link text-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
        <?php endforeach; ?>
    </ul><!-- End Notification Dropdown Items -->
</li><!-- End Notification Nav -->

      <!--  <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number"></span>
          </a> 

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">
              You have 3 new messages
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Maria Hudson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>4 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-2.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Anna Nelson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>6 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-3.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>David Muldon</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>8 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="dropdown-footer">
              <a href="#">Show all messages</a>
            </li>

          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->

        <li class="nav-item dropdown pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $username; ?></span>
        </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>Welcome <?php echo $username; ?></h6>
              
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="userdashboard.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bank"></i><span>Expense</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="expense.php">
              <i class="bi bi-circle"></i><span>Add new</span>
            </a>
          </li>
          <li>
            <a href="expensereport.php">
              <i class="bi bi-circle"></i><span>View report</span>
            </a>
          </li>
        </ul>
      </li><!-- End Components Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-journal-text"></i><span>Budget Limits</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="budget.php">
              <i class="bi bi-circle"></i><span>View/Add new</span>
            </a>
          </li>
          <li>
            <a href="budgetreport.php">
              <i class="bi bi-circle"></i><span>View report</span>
            </a>
          </li>
        </ul>
      </li><!-- End Forms Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-piggy-bank"></i><span>Savings/Goal</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="tables-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="goals.php">
              <i class="bi bi-circle"></i><span>View/Add new</span>
            </a>
          </li>
        </ul>
      </li><!-- End Tables Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bell-fill"></i><span>Bill reminders</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="bill.php">
              <i class="bi bi-circle"></i><span>Add/View</span>
            </a>
          </li>
        </ul>
      </li><!-- End Charts Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-cash"></i><span>Category</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="categories.php">
              <i class="bi bi-circle"></i><span>Add/View</span>
            </a>
          </li>
        </ul>
      </li><!-- End Charts Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" href="users-profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li><!-- End Profile Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="#">
          <i class="bi bi-question-circle"></i>
          <span>F.A.Q</span>
        </a>
      </li><!-- End F.A.Q Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="#">
          <i class="bi bi-envelope"></i>
          <span>Contact</span>
        </a>
      </li><!-- End Contact Page Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="logout.php">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Logout</span>
        </a>
      </li><!-- End Login Page Nav -->

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">

            <!-- expense Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">

              <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item" href="#" onclick="showTotal('today')">Today</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showTotal('month')">This Month</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showTotal('year')">This Year</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Total Expense <span id="time-span">| Today</span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                        <div class="ps-3">
                            
                            <h6 id="total-amount"><?php echo $totalToday; ?></h6>
                          <!--  <span class="text-success small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">increase</span>-->
                        </div>
                    </div>
                </div>
              </div>
            </div><!-- End expense Card -->
            <script>
        const totals = {
            today: <?php echo $totalToday; ?>,
            month: <?php echo $totalMonth; ?>,
            year: <?php echo $totalYear; ?>
        };

        function showTotal(period) {
            document.getElementById('total-amount').innerText = totals[period];
            document.getElementById('time-span').innerText = `| ${capitalize(period)}`;
        }

        function capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    </script>

            <!-- budget Card -->
            <div class="col-xxl-4 col-md-6">
            <div class="card info-card revenue-card">
                <div class="card-body">
                    <h5 class="card-title">Prosper Purse <span>| This Month</span></h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-handbag"></i>
                        </div>
                        <div class="ps-3">
                            <h6 id="budget-amount" style="font-size: 22px;">₹ <?php echo $totalBudget; ?></h6>
                            <!-- <span class="text-success small pt-1 fw-bold" style="font-size: smaller;">8%</span> 
                           <span class="text-muted small pt-2 ps-1" style="font-size: smaller;">increase</span> -->
                        </div>

                    </div>
                </div>
            </div>
            </div><!-- End budget Card -->
            <script>
        const totals = {
            today: <?php echo $totalToday; ?>,
            month: <?php echo $totalMonth; ?>,
            year: <?php echo $totalYear; ?>
        };

        function showTotal(period) {
            document.getElementById('total-amount').innerText = totals[period];
            document.getElementById('time-span').innerText = `| ${capitalize(period)}`;
        }

        function capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    </script>

            <!-- Customers Card -->
            <div class="col-xxl-4 col-xl-12">

              <div class="card info-card customers-card">

                <div class="filter">
                  <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                </div>

                <div class="card-body">
                  <h5 class="card-title">Remaining Amount </h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div class="ps-3">
                    <h6 id="budget-amount" style="font-size: 22px;"><?php echo number_format($remainingAmount, 2); ?></h6>
                    <!--  <span class="text-danger small pt-1 fw-bold">12%</span> <span class="text-muted small pt-2 ps-1">decrease</span> -->

                    </div>
                  </div>

                </div>
              </div>

            </div><!-- End Customers Card -->

          
<!-- ExpenseBudget -->
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Expense Report</h5>
            <!-- Line Chart -->
            <div id="reportsChart"></div>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const dates = <?php echo $dates_json; ?>;
                    const expenses = <?php echo $expenses_json; ?>;
                    const categories = <?php echo $categories_json; ?>;

                    const chart = new ApexCharts(document.querySelector("#reportsChart"), {
                        series: [{
                            name: 'Expense',
                            data: expenses,
                        }],
                        chart: {
                            height: 350,
                            type: 'area',
                            toolbar: {
                                show: false
                            },
                        },
                        markers: {
                            size: 4
                        },
                        colors: ['#4154f1', '#2eca6a', '#ff771d'],
                        fill: {
                            type: "gradient",
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.4,
                                stops: [0, 90, 100]
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        xaxis: {
                            type: 'datetime',
                            categories: dates
                        },
                        tooltip: {
                            x: {
                                format: 'dd/MM/yy'
                            },
                            y: {
                                formatter: function(value, { seriesIndex, dataPointIndex, w }) {
                                    const category = categories[dataPointIndex];
                                    return `₹ ${value}<br>Category: ${category}`;
                                }
                            }
                        }
                    });
                    chart.render();
                });
            </script>
            <!-- End Line Chart -->
        </div>
    </div>
</div><!-- End ExpenseBudget -->
        <script>
        const totals = {
            today: <?php echo $totalToday; ?>,
            month: <?php echo $totalMonth; ?>,
            year: <?php echo $totalYear; ?>
        };

        function showTotal(period) {
            document.getElementById('total-amount').innerText = totals[period];
            document.getElementById('time-span').innerText = `| ${capitalize(period)}`;
        }

        function filterChart(period) {
            document.getElementById('report-time-span').innerText = `/${capitalize(period)}`;
            // Implement chart data filtering based on the period (today, month, year)
        }

        function capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    </script>

            
            <div class="col-12">
            <div class="col-md-12 col-xl-4 grid-margin stretch-card">
              <div class="card" style="border: 1px solid #ddd; border-radius: 10px; padding: 20px; background-color: #fff; width: 750px">
                <div class="card-body">
                    <h4 class="card-title" style="font-size: 1.5rem; font-weight: bold; color: #333;">To Do List</h4>
                      <div class="add-items d-flex" style="margin-bottom: 20px;">
                          <input type="text" id="taskInput" class="form-control todo-list-input" placeholder="enter task.." style="flex: 1; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-right: 10px;">
                          <button class="add btn btn-primary todo-list-add-btn" onclick="addTask()" style="padding: 10px 20px; border-radius: 5px;">Add</button>
                      </div>
                      <ul id="todo-list" style="list-style-type: none; padding: 10px;">
                          <!-- List items will be added here dynamically -->
                          <?php
                          require_once 'config.php'; // Database configuration file
                          require_once 'conn.php';   // Database connection file
                          $user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

                          // Fetch existing tasks from the database
                          $stmt = $conn->prepare("SELECT todo_id, task FROM ToDoList WHERE user_id = ?");
                          $stmt->bind_param("i", $user_id);
                          $stmt->execute();
                          $result = $stmt->get_result();

                          while ($row = $result->fetch_assoc()) {
                              echo '<li class="todo-list-item" data-todo-id="' . $row['todo_id'] . '">';
                              echo '<span class="task-text">' . htmlspecialchars($row['task']) . '</span>';
                              echo '<button class="delete-btn" onclick="deleteTask(' . $row['todo_id'] . ', this.parentNode)">✖</button>';
                              echo '</li>';
                          }

                          $stmt->close();
                          $conn->close();
                          ?>
                      </ul>
                </div>
              </div>
            </div>
            </div> 

            

          </div>
        </div><!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">

          <!-- Recent Activity -->
          <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              
            </div>

            <div class="card-body">
              <h5 class="card-title">Recent Activity <span>| Today</span></h5>

              <div class="activity">
                <?php if (!empty($goals)): ?>
                    <?php foreach ($goals as $goal): ?>
                        <div class="activity-item d-flex">
                          <div class="activite-label"><?php echo htmlspecialchars($goal['target_date']); ?></div>
                          <i class='bi bi-circle-fill activity-badge text-success align-self-start'></i>
                          <div class="activity-content">
                            <strong><?php echo htmlspecialchars($goal['goal_name']); ?></strong>
                            <br>Current Amount: ₹ <?php echo htmlspecialchars($goal['current_amount']); ?>
                            <br>Target Amount: ₹ <?php echo htmlspecialchars($goal['target_amount']); ?>
                          </div>
                        </div><!-- End activity item-->
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No recent Goals found.</p>
                <?php endif; ?>
              </div>


              </div>

            </div>
          </div><!-- End Recent Activity -->

          
        

      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="credits">
     </a>
    </div>
  </footer><!-- End Footer -->

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
    function addTask() {
        var taskInput = document.getElementById('taskInput');
        var taskText = taskInput.value.trim();

        if (taskText !== "") {
            // Send task to server
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "add_task.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Append new task to list if successfully added to DB
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        var todoList = document.getElementById('todo-list');
                        var listItem = document.createElement('li');
                        listItem.className = 'todo-list-item';
                        listItem.dataset.todoId = response.todo_id;

                        var taskSpan = document.createElement('span');
                        taskSpan.className = 'task-text';
                        taskSpan.textContent = taskText;

                        var deleteButton = document.createElement('button');
                        deleteButton.className = 'delete-btn';
                        deleteButton.textContent = '✖';
                        deleteButton.onclick = function() {
                            deleteTask(response.todo_id, listItem);
                        };

                        listItem.appendChild(taskSpan);
                        listItem.appendChild(deleteButton);
                        todoList.appendChild(listItem);

                        taskInput.value = ""; // Clear the input field
                    } else {
                        alert('Failed to add task');
                    }
                }
            };
            xhr.send("task=" + encodeURIComponent(taskText));
        }
    }

    function deleteTask(todoId, listItem) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_task.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    listItem.remove();
                } else {
                    alert('Failed to delete task');
                }
            }
        };
        xhr.send("todo_id=" + todoId);
    }
</script>
</body>

</html>