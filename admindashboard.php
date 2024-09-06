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

// SQL Query to count users with role_id = 1
$queryuser = "SELECT COUNT(*) AS user_count FROM Users WHERE role_id = 1";

// Prepare and execute the query
$stmtuser = $conn->prepare($queryuser);
if (!$stmtuser) {
    die("Preparation failed: " . $conn->error);
}

if (!$stmtuser->execute()) {
    die("Execution failed: " . $stmtuser->error);
}

// Get the result
$resultuser = $stmtuser->get_result();
if (!$resultuser) {
    die("Getting result failed: " . $stmtuser->error);
}

$totaluser = $resultuser->fetch_assoc()['user_count'] ?? 0;

// SQL Query to count users with role_id != 1 
$queryadmin = "SELECT COUNT(*) AS user_count FROM Users WHERE role_id != 1";

// Prepare and execute the query
$stmtadmin = $conn->prepare($queryadmin);
if (!$stmtadmin) {
    die("Preparation failed: " . $conn->error);
}

if (!$stmtadmin->execute()) {
    die("Execution failed: " . $stmtadmin->error);
}

// Get the result
$resultadmin = $stmtadmin->get_result();
if (!$resultadmin) {
    die("Getting result failed: " . $stmtadmin->error);
}

$totaladmin= $resultadmin->fetch_assoc()['user_count'] ?? 0;



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
      <h1>Admin Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item active">Admin Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-8">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">

            

                <div class="card-body">
                  <h5 class="card-title">Users</h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $totaluser; ?></h6>
                      
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Sales Card -->

            <!-- Revenue Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">

                <div class="card-body">
                  <h5 class="card-title">Admins</h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $totaladmin; ?></h6>
                     
                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Revenue Card -->

            <!-- Customers Card 
            <div class="col-xxl-4 col-xl-12">

              <div class="card info-card customers-card">

                <div class="card-body">
                  <h5 class="card-title">Notifications Sent <span>| This Year</span></h5>

                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-bell-fill"></i>
                    </div>
                    <div class="ps-3">
                      <h6>1244</h6>
                     
                    </div>
                  </div>

                </div>
              </div>

            </div> End Customers Card -->

            <!-- Reports 
            <div class="col-12">
              <div class="card">

                <div class="card-body">
                  <h5 class="card-title">Notifications Sent</h5>

               
                  <div id="reportsChart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      new ApexCharts(document.querySelector("#reportsChart"), {
                        series: [{
                          name: 'Admin',
                          data: [31, 40, 28, 51, 42, 82, 56],
                        }, {
                          name: 'Users',
                          data: [11, 32, 45, 32, 34, 52, 41]
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
                          categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
                        },
                        tooltip: {
                          x: {
                            format: 'dd/MM/yy HH:mm'
                          },
                        }
                      }).render();
                    });
                  </script>
                 

                </div>

              </div>
            </div>  End Reports -->

            

           

          </div>
        </div><!-- End Left side columns -->

        <!-- Right side columns -->
        <div class="col-lg-4">

          <!-- Recent Activity 
          <div class="card">
  
            <div class="card-body">
              <h5 class="card-title">Recent Activity <span>| Today</span></h5>

              <div class="activity">

                

              </div>

            </div>
          </div><!-- End Recent Activity -->

 
          
        </div><!-- End Right side columns -->

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