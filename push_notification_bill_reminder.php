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
echo "Welcome, " . $welcome_message;
// Database connection
require_once 'config.php';
require_once 'conn.php';

// Fetch bills from the database
$query = "SELECT * FROM bills ";
$stmt = $conn->prepare($query);

// Error handling for preparing statement
if (!$stmt) {
    die('Error preparing statement: ' . htmlspecialchars($conn->error));
}



// Error handling for binding parameters
if (!$stmt) {
    die('Error binding parameters: ' . htmlspecialchars($stmt->error));
}

// Execute the statement
if (!$stmt->execute()) {
    die('Error executing the query: ' . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result();

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
            <h1>Push Bill Reminder</h1>
            <button onclick="sendNotification('sendmail.php')">Send Bill Reminder Email</button>
            <table>
                <thead>
                    <tr>
                        <th>Bill Id</th>
                        <th>User Id</th>
                        <th>Bill Name</th>
                        <th>Amount Due</th>
                        <th>Due Date</th>
                        <th>Reminder Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Check if there are rows fetched
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['bill_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['bill_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['amount_due']); ?></td>
                                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['reminder_date']); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr><td colspan="6">No bills found.</td></tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <script>
            function sendNotification(action) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "sendmail.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            alert("Email sent successfully!");
                            // Alternatively, you can show a custom popup or message div
                            // Example: document.getElementById('message').innerHTML = "Email sent successfully!";
                        } else {
                            alert("Failed to send email. Please try again later.");
                        }
                    }
                };
                xhr.send("action=" + action);
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
