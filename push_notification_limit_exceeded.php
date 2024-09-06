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
// Database connection
require_once 'config.php'; // Adjust path as per your configuration
require_once 'conn.php';   // Adjust path as per your configuration

// Initialize an array to store exceeded budgets
$_SESSION['exceeded_budgets'] = [];

// Query to fetch budgets that have exceeded the limit
$query_exceeded_budgets = "
    SELECT 
        b.user_id, 
        b.category_id, 
        b.amount_limit, 
        SUM(e.amount) AS total_expense
    FROM 
        Budgets b
    JOIN 
        Expenses e 
    ON 
        b.user_id = e.user_id 
        AND b.category_id = e.category_id
    WHERE 
        b.month = MONTH(CURRENT_DATE)
        AND b.year = YEAR(CURRENT_DATE)

    GROUP BY 
        b.user_id, b.category_id
    HAVING 
        total_expense > b.amount_limit
";

$stmt_exceeded_budgets = $conn->prepare($query_exceeded_budgets);
if (!$stmt_exceeded_budgets) {
    die('Error preparing statement: ' . $conn->error);
}

$stmt_exceeded_budgets->execute();
if (!$stmt_exceeded_budgets) {
    die('Error executing statement: ' . $stmt_exceeded_budgets->error);
}

$result_exceeded_budgets = $stmt_exceeded_budgets->get_result();
while ($row = $result_exceeded_budgets->fetch_assoc()) {
    $_SESSION['exceeded_budgets'][] = $row;
}

$stmt_exceeded_budgets->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'head.html'; ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php include 'admin-body.php'; ?>

    <main id="main" class="main">
        <div class="container">
            <h1>Exceeded Budgets</h1>
            <button onclick="sendNotification()">Send limit exceeded Email</button>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Category ID</th>
                        <th>Amount Limit</th>
                        <th>Total Expense</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($_SESSION['exceeded_budgets'])) {
                        foreach ($_SESSION['exceeded_budgets'] as $budget) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($budget['user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($budget['category_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($budget['amount_limit']) . "</td>";
                            echo "<td>" . htmlspecialchars($budget['total_expense']) . "</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <script>
            function sendNotification() {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "mail_limit_exceeded.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            alert("Email sent successfully!");
                        } else {
                            alert("Failed to send email. Please try again later.");
                        }
                    }
                };
                xhr.send();
            }
        </script>
    </main>

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
